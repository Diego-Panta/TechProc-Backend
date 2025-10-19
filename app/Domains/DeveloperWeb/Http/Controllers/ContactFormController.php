<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Services\ContactFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ContactFormController
{
    public function __construct(
        private ContactFormService $contactFormService
    ) {}

    // Para el frontend público - mostrar formulario
    public function create(): View
    {
        return view('developer-web.contact-form.create');
    }

    // Para el frontend público - procesar formulario
    public function store(\App\Domains\DeveloperWeb\Http\Requests\StoreContactFormRequest $request): RedirectResponse
    {
        try {
            Log::info('Datos recibidos:', $request->all());

            $contactForm = $this->contactFormService->createContactForm($request->validated());

            Log::info('Formulario creado:', ['id' => $contactForm->id]);

            return redirect()->back()
                ->with('success', '¡Gracias por contactarnos! Te responderemos pronto.');
        } catch (\Exception $e) {
            Log::error('Error detallado:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Para el panel del desarrollador - listar consultas con filtros
    public function index(): View
    {
        $filters = [
            'status' => request('status', 'all'),
            'assigned_to' => request('assigned_to'),
            'form_type' => request('form_type'),
        ];

        $contactForms = $this->contactFormService->getAllContactForms($filters);

        $statuses = [
            'all' => 'Todos',
            'pending' => 'Pendientes',
            'in_progress' => 'En Progreso',
            'responded' => 'Respondidos',
            'spam' => 'Spam'
        ];

        $formTypes = $this->contactFormService->getFormTypes();
        $assignedEmployees = $this->contactFormService->getAssignedEmployees();
        
        return view('developer-web.contact-form.index', compact(
            'contactForms', 
            'filters', 
            'statuses',
            'formTypes',
            'assignedEmployees'
        ));
    }

    // Para el panel del desarrollador - mostrar detalles
    public function show(int $id): View
    {
        $contactForm = $this->contactFormService->getContactFormById($id);

        if (!$contactForm) {
            abort(404);
        }

        return view('developer-web.contact-form.show', compact('contactForm'));
    }

    // Para el panel del desarrollador - marcar como spam
    public function markAsSpam(int $id): RedirectResponse
    {
        try {
            $success = $this->contactFormService->markAsSpam($id);

            if ($success) {
                return redirect()->route('developer-web.contact-forms.index')
                    ->with('success', 'Consulta marcada como spam correctamente.');
            }

            return redirect()->route('developer-web.contact-forms.index')
                ->with('error', 'No se pudo marcar la consulta como spam.');
        } catch (\Exception $e) {
            Log::error('Error al marcar contacto como spam', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('developer-web.contact-forms.index')
                ->with('error', 'Error al procesar la solicitud.');
        }
    }

    // Para el panel del desarrollador - responder consulta
    public function respond(int $id): RedirectResponse
    {
        try {
            // Validación manual
            $response = request('response');
            
            if (empty($response)) {
                return redirect()->route('developer-web.contact-forms.show', $id)
                    ->with('error', 'La respuesta es obligatoria.')
                    ->withInput();
            }
            
            if (strlen(trim($response)) < 10) {
                return redirect()->route('developer-web.contact-forms.show', $id)
                    ->with('error', 'La respuesta debe tener al menos 10 caracteres.')
                    ->withInput();
            }

            // TEMPORAL: Asignar un empleado de prueba (en producción esto vendría del usuario autenticado)
            $assignedTo = 1; // ID del empleado de prueba

            $success = $this->contactFormService->respondToContact($id, $response, $assignedTo);
            
            if ($success) {
                return redirect()->route('developer-web.contact-forms.index')
                    ->with('success', 'Respuesta enviada correctamente.');
            }

            return redirect()->route('developer-web.contact-forms.show', $id)
                ->with('error', 'No se pudo enviar la respuesta.');
                
        } catch (\Exception $e) {
            Log::error('Error al responder contacto', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('developer-web.contact-forms.show', $id)
                ->with('error', 'Error al procesar la respuesta: ' . $e->getMessage());
        }
    }
}