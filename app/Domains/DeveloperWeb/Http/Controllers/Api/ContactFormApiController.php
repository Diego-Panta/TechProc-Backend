<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Http\Requests\Api\RespondContactFormApiRequest;
use App\Domains\DeveloperWeb\Http\Requests\Api\StoreContactFormApiRequest;
use App\Domains\DeveloperWeb\Services\ContactFormService;
use App\Domains\DeveloperWeb\Enums\ContactFormStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContactFormApiController
{
    public function __construct(
        private ContactFormService $contactFormService
    ) {}

    /**
     * Listar formularios de contacto (PROTEGIDO)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Obtener usuario autenticado desde el middleware
            $user = $request->user();
            
            $filters = [
                'status' => $request->get('status', 'all'),
                'assigned_to' => $request->get('assigned_to'),
                'form_type' => $request->get('form_type'),
            ];

            $perPage = $request->get('per_page', 15);

            $contactForms = $this->contactFormService->getAllContactForms($filters, $perPage);

            // Log de acceso
            Log::info('Usuario accedió a listado de contact forms', [
                'user_id' => $user->id,
                'email' => $user->email,
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => $contactForms
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing contact forms', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Responder a formulario de contacto (PROTEGIDO)
     */
    public function respond(RespondContactFormApiRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Buscar el empleado asociado al usuario autenticado
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un empleado asociado a tu usuario. Contacta al administrador.'
                ], 400);
            }

            $assignedTo = $employee->id;

            $success = $this->contactFormService->respondToContact($id, $request->response, $assignedTo);
            
            if ($success) {
                // Obtener el contacto actualizado para el log
                $contactForm = $this->contactFormService->getContactFormById($id);
                
                Log::info('Usuario respondió contacto y se envió email', [
                    'user_id' => $user->id,
                    'employee_id' => $employee->id,
                    'contact_form_id' => $id,
                    'user_email' => $contactForm->email
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Respuesta enviada correctamente y notificación por email enviada al usuario.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar la respuesta.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error responding to contact form', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Asignar formulario a mi usuario (PROTEGIDO)
     */
     public function assignToMe(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un empleado asociado a tu usuario.'
                ], 400);
            }

            $contactForm = $this->contactFormService->getContactFormById($id);
            
            if (!$contactForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formulario de contacto no encontrado'
                ], 404);
            }

            // Validar que no esté en estado final
            if ($contactForm->isFinalized()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede asignar un formulario en estado final (' . $contactForm->getStatusLabel() . ')'
                ], 400);
            }

            // Actualizar asignación y cambiar estado
            $success = $this->contactFormService->updateContactFormAssignment($id, $employee->id);
            
            if ($success) {
                Log::info('Usuario asignó contacto a sí mismo', [
                    'user_id' => $user->id,
                    'employee_id' => $employee->id,
                    'contact_form_id' => $id,
                    'new_status' => ContactFormStatus::IN_PROGRESS->value
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Formulario asignado correctamente a tu usuario.',
                    'data' => [
                        'new_status' => ContactFormStatus::IN_PROGRESS->value,
                        'status_label' => ContactFormStatus::IN_PROGRESS->label(),
                        'assigned_to' => $employee->id
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo asignar el formulario.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error assigning contact form', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el formulario',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Actualizar estado del formulario (PROTEGIDO)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validación manual para mejor control de errores
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:' . implode(',', ContactFormStatus::values())
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                    'valid_statuses' => ContactFormStatus::values()
                ], 422);
            }

            $validated = $validator->validated();

            $contactForm = $this->contactFormService->getContactFormById($id);
            
            if (!$contactForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formulario de contacto no encontrado'
                ], 404);
            }

            // Obtener el estado actual de forma segura
            $currentStatus = $contactForm->getRawStatus();
            
            // Validar transiciones de estado
            if ($contactForm->isFinalized() && $validated['status'] !== $currentStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede modificar un formulario en estado final (' . $contactForm->getStatusLabel() . ')'
                ], 400);
            }

            $success = $this->contactFormService->updateContactFormStatus($id, $validated['status']);
            
            if ($success) {
                Log::info('Usuario actualizó estado de contacto', [
                    'user_id' => $user->id,
                    'contact_form_id' => $id,
                    'old_status' => $currentStatus,
                    'new_status' => $validated['status']
                ]);

                $newStatus = ContactFormStatus::tryFrom($validated['status']);

                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente.',
                    'data' => [
                        'new_status' => $validated['status'],
                        'status_label' => $newStatus->label(),
                        'contact_form_id' => $id
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar el estado.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error updating contact form status', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al actualizar el estado',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

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

    /**
     * Obtener opciones de estados (PROTEGIDO)
     */
    public function getStatusOptions(Request $request): JsonResponse
    {
        try {
            // Verificar que el usuario esté autenticado (si es una ruta protegida)
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 401);
            }

            $statusOptions = [
                'statuses' => ContactFormStatus::labels(),
                'active_statuses' => [],
                'final_statuses' => []
            ];

            // Obtener estados activos
            foreach (ContactFormStatus::getActiveStatuses() as $activeStatus) {
                $statusOptions['active_statuses'][$activeStatus] = ContactFormStatus::labels()[$activeStatus];
            }

            // Obtener estados finales
            foreach (ContactFormStatus::getFinalStatuses() as $finalStatus) {
                $statusOptions['final_statuses'][$finalStatus] = ContactFormStatus::labels()[$finalStatus];
            }

            return response()->json([
                'success' => true,
                'data' => $statusOptions
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting status options', [
                'user_id' => $request->user()->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las opciones de estado',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener estadísticas mejoradas (PROTEGIDO)
     */
    public function getEnhancedStats(): JsonResponse
    {
        try {
            $stats = $this->contactFormService->getEnhancedStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting enhanced stats', [
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