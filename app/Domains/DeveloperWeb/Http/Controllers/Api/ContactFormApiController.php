<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Http\Requests\Api\RespondContactFormApiRequest;
use App\Domains\DeveloperWeb\Http\Requests\Api\StoreContactFormApiRequest;
use App\Domains\DeveloperWeb\Services\ContactFormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactFormApiController
{
    public function __construct(
        private ContactFormService $contactFormService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/developer-web/contact-forms",
     *     summary="Listar formularios de contacto",
     *     tags={"Contact Forms"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"all", "pending", "in_progress", "responded", "spam"})
     *     ),
     *     @OA\Parameter(
     *         name="form_type",
     *         in="query",
     *         description="Filtrar por tipo de formulario",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="assigned_to",
     *         in="query",
     *         description="Filtrar por empleado asignado",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página para paginación",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de formularios de contacto",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/ContactForm")
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->get('status', 'all'),
                'assigned_to' => $request->get('assigned_to'),
                'form_type' => $request->get('form_type'),
            ];

            $perPage = $request->get('per_page', 15);

            $contactForms = $this->contactFormService->getAllContactForms($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $contactForms
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing contact forms', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los formularios de contacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/contact-forms/{id}",
     *     summary="Obtener detalles de un formulario de contacto",
     *     tags={"Contact Forms"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del formulario de contacto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del formulario de contacto",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ContactForm")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulario no encontrado"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $contactForm = $this->contactFormService->getContactFormById($id);

            if (!$contactForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formulario de contacto no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $contactForm
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing contact form', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el formulario de contacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/contact-forms",
     *     summary="Crear un nuevo formulario de contacto",
     *     tags={"Contact Forms"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name", "email", "subject", "message"},
     *             @OA\Property(property="full_name", type="string", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255),
     *             @OA\Property(property="phone", type="string", maxLength=20, nullable=true),
     *             @OA\Property(property="company", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="subject", type="string", maxLength=255),
     *             @OA\Property(property="message", type="string", minLength=10),
     *             @OA\Property(property="form_type", type="string", maxLength=50, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Formulario creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ContactForm"),
     *             @OA\Property(property="message", type="string", example="¡Gracias por contactarnos! Te responderemos pronto.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(StoreContactFormApiRequest $request): JsonResponse
    {
        try {
            $contactForm = $this->contactFormService->createContactForm($request->validated());

            return response()->json([
                'success' => true,
                'data' => $contactForm,
                'message' => '¡Gracias por contactarnos! Te responderemos pronto.'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Error creating contact form', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el formulario de contacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/contact-forms/{id}/respond",
     *     summary="Responder a un formulario de contacto",
     *     tags={"Contact Forms"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del formulario de contacto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"response"},
     *             @OA\Property(property="response", type="string", minLength=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta enviada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Respuesta enviada correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulario no encontrado"
     *     )
     * )
     */
    public function respond(RespondContactFormApiRequest $request, int $id): JsonResponse
    {
        try {
            // TEMPORAL: Buscar un empleado activo para asignar
            $employee = \App\Domains\Administrator\Models\Employee::where('employment_status', 'Active')->first();
            $assignedTo = $employee ? $employee->id : null;

            $success = $this->contactFormService->respondToContact($id, $request->response, $assignedTo);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Respuesta enviada correctamente.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar la respuesta.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error responding to contact form', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la respuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/contact-forms/{id}/spam",
     *     summary="Marcar formulario como spam",
     *     tags={"Contact Forms"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del formulario de contacto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Formulario marcado como spam",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Consulta marcada como spam correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Formulario no encontrado"
     *     )
     * )
     */
    public function markAsSpam(int $id): JsonResponse
    {
        try {
            $success = $this->contactFormService->markAsSpam($id);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Consulta marcada como spam correctamente.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo marcar la consulta como spam.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error marking contact form as spam', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/contact-forms/stats/summary",
     *     summary="Obtener estadísticas de formularios de contacto",
     *     tags={"Contact Forms"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="pending", type="integer", example=10),
     *                 @OA\Property(property="in_progress", type="integer", example=5),
     *                 @OA\Property(property="responded", type="integer", example=8),
     *                 @OA\Property(property="spam", type="integer", example=2)
     *             )
     *         )
     *     )
     * )
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->contactFormService->getContactStats();
            
            $total = array_sum($stats);

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'pending' => $stats['pending'] ?? 0,
                    'in_progress' => $stats['in_progress'] ?? 0,
                    'responded' => $stats['responded'] ?? 0,
                    'spam' => $stats['spam'] ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting contact form stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}