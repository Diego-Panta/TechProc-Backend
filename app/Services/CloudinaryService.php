<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CloudinaryService
{
    /**
     * Subir una imagen a Cloudinary y retornar la URL pública
     *
     * @param UploadedFile $file
     * @param string|null $customName Nombre personalizado para el archivo
     * @param string $folder Carpeta en Cloudinary
     * @return string URL pública del archivo en Cloudinary
     */
    public function uploadImage(UploadedFile $file, ?string $customName = null, string $folder = 'techproc/news'): string
    {
        try {
            // Validar que sea una imagen
            if (!Str::startsWith($file->getMimeType(), 'image/')) {
                throw new \Exception('El archivo debe ser una imagen');
            }

            // Generar public_id único
            $publicId = $customName
                ? $this->sanitizeFileName($customName)
                : Str::uuid()->toString();

            // Subir el archivo a Cloudinary usando el UploadApi
            $cloudinary = app(\Cloudinary\Cloudinary::class);
            $uploadResult = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'public_id' => $publicId,
            ]);

            // Log para debug
            Log::debug('Cloudinary upload result', [
                'result' => $uploadResult
            ]);

            // Obtener URL segura desde el array de respuesta
            $secureUrl = $uploadResult['secure_url'] ?? null;

            if (!$secureUrl) {
                throw new \Exception('No se pudo obtener la URL de Cloudinary');
            }

            Log::info('Imagen subida a Cloudinary', [
                'public_id' => $uploadResult['public_id'] ?? null,
                'file_name' => $file->getClientOriginalName(),
                'url' => $secureUrl,
                'size' => $file->getSize(),
            ]);

            return $secureUrl;

        } catch (\Exception $e) {
            Log::error('Error al subir imagen a Cloudinary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $file->getClientOriginalName()
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar una imagen de Cloudinary
     *
     * @param string $publicIdOrUrl Public ID o URL completa
     * @return bool
     */
    public function deleteImage(string $publicIdOrUrl): bool
    {
        try {
            $publicId = $this->extractPublicId($publicIdOrUrl);

            if (!$publicId) {
                Log::warning('No se pudo extraer el Public ID para eliminar', [
                    'input' => $publicIdOrUrl
                ]);
                return false;
            }

            $cloudinary = app(\Cloudinary\Cloudinary::class);
            $result = $cloudinary->uploadApi()->destroy($publicId);

            $success = isset($result['result']) && $result['result'] === 'ok';

            if ($success) {
                Log::info('Imagen eliminada de Cloudinary', [
                    'public_id' => $publicId
                ]);
            } else {
                Log::warning('No se pudo eliminar la imagen de Cloudinary', [
                    'public_id' => $publicId,
                    'result' => $result
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Error al eliminar imagen de Cloudinary', [
                'error' => $e->getMessage(),
                'public_id_or_url' => $publicIdOrUrl
            ]);
            return false;
        }
    }

    /**
     * Actualizar una imagen (eliminar la anterior y subir la nueva)
     *
     * @param string|null $oldPublicIdOrUrl
     * @param UploadedFile $newFile
     * @param string|null $customName
     * @param string $folder
     * @return string Nueva URL pública
     */
    public function updateImage(
        ?string $oldPublicIdOrUrl,
        UploadedFile $newFile,
        ?string $customName = null,
        string $folder = 'techproc/news'
    ): string {
        // Eliminar la imagen anterior si existe
        if ($oldPublicIdOrUrl) {
            $this->deleteImage($oldPublicIdOrUrl);
        }

        // Subir la nueva imagen
        return $this->uploadImage($newFile, $customName, $folder);
    }

    /**
     * Extraer el Public ID desde una URL de Cloudinary
     *
     * @param string $publicIdOrUrl
     * @return string|null
     */
    protected function extractPublicId(string $publicIdOrUrl): ?string
    {
        // Si ya es un public_id (no contiene http/https)
        if (!Str::startsWith($publicIdOrUrl, ['http://', 'https://'])) {
            return $publicIdOrUrl;
        }

        // Extraer public_id de URL de Cloudinary
        // Formato: https://res.cloudinary.com/{cloud_name}/{resource_type}/{type}/{version}/{public_id}.{format}
        // Ejemplo: https://res.cloudinary.com/demo/image/upload/v1234567890/techproc/news/my-image.jpg

        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)\.\w+$/', $publicIdOrUrl, $matches)) {
            return $matches[1];
        }

        // Si no se puede extraer, intentar con el path completo después de upload/
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+)$/', $publicIdOrUrl, $matches)) {
            return rtrim($matches[1], '/');
        }

        return null;
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
     * Verificar si una imagen existe en Cloudinary
     *
     * @param string $publicIdOrUrl
     * @return bool
     */
    public function imageExists(string $publicIdOrUrl): bool
    {
        try {
            $publicId = $this->extractPublicId($publicIdOrUrl);

            if (!$publicId) {
                return false;
            }

            $cloudinary = app(\Cloudinary\Cloudinary::class);
            $result = $cloudinary->adminApi()->asset($publicId);

            return isset($result['public_id']);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener información de una imagen
     *
     * @param string $publicIdOrUrl
     * @return array|null
     */
    public function getImageInfo(string $publicIdOrUrl): ?array
    {
        try {
            $publicId = $this->extractPublicId($publicIdOrUrl);

            if (!$publicId) {
                return null;
            }

            $cloudinary = app(\Cloudinary\Cloudinary::class);
            $resource = $cloudinary->adminApi()->asset($publicId);

            return [
                'public_id' => $resource['public_id'] ?? null,
                'url' => $resource['secure_url'] ?? null,
                'format' => $resource['format'] ?? null,
                'width' => $resource['width'] ?? null,
                'height' => $resource['height'] ?? null,
                'size' => $resource['bytes'] ?? null,
                'created_at' => $resource['created_at'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Error al obtener info de imagen de Cloudinary', [
                'error' => $e->getMessage(),
                'public_id_or_url' => $publicIdOrUrl
            ]);
            return null;
        }
    }
}
