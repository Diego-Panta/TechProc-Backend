<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleDriveService
{
    protected Client $client;
    protected Drive $driveService;
    protected string $folderId;

    protected bool $initialized = false;

    public function __construct()
    {
        // No inicializamos el cliente en el constructor
        // Se inicializará lazy cuando sea necesario
    }

    /**
     * Inicializar el cliente de Google (lazy initialization)
     */
    protected function initializeClient(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->client = new Client();
        $this->client->setApplicationName(config('services.google_drive.app_name', 'TechProc Backend'));

        // Configurar autenticación con Service Account
        $credentialsPath = config('services.google_drive.credentials_path');

        if (!file_exists($credentialsPath)) {
            throw new \Exception("Google Drive credentials file not found at: {$credentialsPath}");
        }

        $this->client->setAuthConfig($credentialsPath);
        $this->client->addScope(Drive::DRIVE_FILE);

        $this->driveService = new Drive($this->client);
        $this->folderId = config('services.google_drive.folder_id');
        $this->initialized = true;
    }

    /**
     * Subir una imagen a Google Drive y retornar la URL pública
     *
     * @param UploadedFile $file
     * @param string|null $customName Nombre personalizado para el archivo
     * @return string URL pública del archivo en Google Drive
     */
    public function uploadImage(UploadedFile $file, ?string $customName = null): string
    {
        // Inicializar cliente si no está inicializado
        $this->initializeClient();

        try {
            // Validar que sea una imagen
            if (!Str::startsWith($file->getMimeType(), 'image/')) {
                throw new \Exception('El archivo debe ser una imagen');
            }

            // Generar nombre único si no se proporciona uno
            $fileName = $customName
                ? $this->sanitizeFileName($customName) . '.' . $file->getClientOriginalExtension()
                : Str::uuid() . '_' . $file->getClientOriginalName();

            // Crear metadata del archivo
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$this->folderId]
            ]);

            // Subir el archivo
            $driveFile = $this->driveService->files->create(
                $fileMetadata,
                [
                    'data' => file_get_contents($file->getRealPath()),
                    'mimeType' => $file->getMimeType(),
                    'uploadType' => 'multipart',
                    'fields' => 'id,webViewLink,webContentLink'
                ]
            );

            // Hacer el archivo público
            $this->makeFilePublic($driveFile->id);

            // Generar URL pública directa
            $publicUrl = $this->getPublicUrl($driveFile->id);

            Log::info('Imagen subida a Google Drive', [
                'file_id' => $driveFile->id,
                'file_name' => $fileName,
                'url' => $publicUrl
            ]);

            return $publicUrl;

        } catch (\Exception $e) {
            Log::error('Error al subir imagen a Google Drive', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw $e;
        }
    }

    /**
     * Hacer un archivo público en Google Drive
     *
     * @param string $fileId
     * @return void
     */
    protected function makeFilePublic(string $fileId): void
    {
        $permission = new Permission([
            'type' => 'anyone',
            'role' => 'reader'
        ]);

        $this->driveService->permissions->create($fileId, $permission);
    }

    /**
     * Obtener la URL pública directa del archivo
     *
     * @param string $fileId
     * @return string
     */
    protected function getPublicUrl(string $fileId): string
    {
        // Formato de URL directa para visualización de imágenes
        return "https://drive.google.com/uc?export=view&id={$fileId}";
    }

    /**
     * Eliminar un archivo de Google Drive
     *
     * @param string $fileIdOrUrl ID del archivo o URL completa
     * @return bool
     */
    public function deleteImage(string $fileIdOrUrl): bool
    {
        // Inicializar cliente si no está inicializado
        $this->initializeClient();

        try {
            $fileId = $this->extractFileId($fileIdOrUrl);

            if (!$fileId) {
                Log::warning('No se pudo extraer el ID del archivo para eliminar', [
                    'input' => $fileIdOrUrl
                ]);
                return false;
            }

            $this->driveService->files->delete($fileId);

            Log::info('Imagen eliminada de Google Drive', [
                'file_id' => $fileId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error al eliminar imagen de Google Drive', [
                'error' => $e->getMessage(),
                'file_id_or_url' => $fileIdOrUrl
            ]);
            return false;
        }
    }

    /**
     * Extraer el ID del archivo desde una URL de Google Drive
     *
     * @param string $fileIdOrUrl
     * @return string|null
     */
    protected function extractFileId(string $fileIdOrUrl): ?string
    {
        // Si ya es un ID (no contiene http/https)
        if (!Str::startsWith($fileIdOrUrl, ['http://', 'https://'])) {
            return $fileIdOrUrl;
        }

        // Extraer ID de URL como: https://drive.google.com/uc?export=view&id=FILE_ID
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $fileIdOrUrl, $matches)) {
            return $matches[1];
        }

        // Extraer ID de URL como: https://drive.google.com/file/d/FILE_ID/view
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $fileIdOrUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Actualizar una imagen (eliminar la anterior y subir la nueva)
     *
     * @param string|null $oldFileIdOrUrl
     * @param UploadedFile $newFile
     * @param string|null $customName
     * @return string Nueva URL pública
     */
    public function updateImage(?string $oldFileIdOrUrl, UploadedFile $newFile, ?string $customName = null): string
    {
        // Inicializar cliente si no está inicializado
        $this->initializeClient();

        // Eliminar la imagen anterior si existe
        if ($oldFileIdOrUrl) {
            $this->deleteImage($oldFileIdOrUrl);
        }

        // Subir la nueva imagen
        return $this->uploadImage($newFile, $customName);
    }

    /**
     * Sanitizar nombre de archivo
     *
     * @param string $fileName
     * @return string
     */
    protected function sanitizeFileName(string $fileName): string
    {
        // Remover caracteres no permitidos
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileName);

        // Limitar longitud
        return Str::limit($fileName, 100, '');
    }

    /**
     * Verificar si un archivo existe en Google Drive
     *
     * @param string $fileIdOrUrl
     * @return bool
     */
    public function fileExists(string $fileIdOrUrl): bool
    {
        // Inicializar cliente si no está inicializado
        $this->initializeClient();

        try {
            $fileId = $this->extractFileId($fileIdOrUrl);

            if (!$fileId) {
                return false;
            }

            $this->driveService->files->get($fileId);
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Listar archivos en la carpeta configurada
     *
     * @param int $limit
     * @return array
     */
    public function listFiles(int $limit = 100): array
    {
        // Inicializar cliente si no está inicializado
        $this->initializeClient();

        try {
            $response = $this->driveService->files->listFiles([
                'q' => "'{$this->folderId}' in parents and trashed=false",
                'pageSize' => $limit,
                'fields' => 'files(id, name, mimeType, createdTime, size, webViewLink)',
                'orderBy' => 'createdTime desc'
            ]);

            return $response->getFiles();

        } catch (\Exception $e) {
            Log::error('Error al listar archivos de Google Drive', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtener información de un archivo
     *
     * @param string $fileIdOrUrl
     * @return array|null
     */
    public function getFileInfo(string $fileIdOrUrl): ?array
    {
        // Inicializar cliente si no está inicializado
        $this->initializeClient();

        try {
            $fileId = $this->extractFileId($fileIdOrUrl);

            if (!$fileId) {
                return null;
            }

            $file = $this->driveService->files->get($fileId, [
                'fields' => 'id, name, mimeType, size, createdTime, modifiedTime, webViewLink'
            ]);

            return [
                'id' => $file->id,
                'name' => $file->name,
                'mime_type' => $file->mimeType,
                'size' => $file->size,
                'created_at' => $file->createdTime,
                'modified_at' => $file->modifiedTime,
                'url' => $this->getPublicUrl($file->id)
            ];

        } catch (\Exception $e) {
            Log::error('Error al obtener info del archivo de Google Drive', [
                'error' => $e->getMessage(),
                'file_id_or_url' => $fileIdOrUrl
            ]);
            return null;
        }
    }
}
