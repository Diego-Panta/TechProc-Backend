<?php

namespace App\Domains\SupportTechnical\Http\Controllers;

use App\Domains\SupportTechnical\Services\TicketReplyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentController extends Controller
{
    protected TicketReplyService $replyService;

    public function __construct(TicketReplyService $replyService)
    {
        $this->replyService = $replyService;
    }

    /**
     * Download an attachment
     * GET /api/support/attachments/{id}/download
     */
    public function download(Request $request, int $id): BinaryFileResponse|JsonResponse
    {
        try {
            $user = $request->user();

            $attachment = $this->replyService->getAttachmentById($id);

            if (!$attachment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            // Authorize
            $this->authorize('download', $attachment);

            $filePath = storage_path('app/public/' . $attachment->path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El archivo físico no existe'
                ], 404);
            }

            $fileName = basename($attachment->path);

            return response()->download($filePath, $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Delete an attachment
     * DELETE /api/support/attachments/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $attachment = $this->replyService->getAttachmentById($id);

            if (!$attachment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            // Authorize - la policy verifica permisos
            $this->authorize('delete', $attachment);

            $this->replyService->deleteAttachment($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Archivo eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
