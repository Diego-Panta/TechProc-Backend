<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Http\Requests\RespondContactFormRequest;
use App\Domains\DeveloperWeb\Http\Requests\StoreContactFormRequest;
use App\Domains\DeveloperWeb\Services\ContactFormService;
use Illuminate\Http\JsonResponse;
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
    public function store(StoreContactFormRequest $request): RedirectResponse
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

    // Para el panel del desarrollador - listar consultas
    public function index(): View
    {
        $status = request('status', 'all');
        
        if ($status === 'all') {
            $contactForms = $this->contactFormService->getAllContactForms();
        } else {
            $contactForms = $this->contactFormService->getContactFormsByStatus($status);
        }

        $statuses = [
            'all' => 'Todos',
            'pending' => 'Pendientes',
            'responded' => 'Respondidos',
            'spam' => 'Spam'
        ];
        
        return view('developer-web.contact-form.index', compact('contactForms', 'status', 'statuses'));
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

            $success = $this->contactFormService->respondToContact($id, $response);
            
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
