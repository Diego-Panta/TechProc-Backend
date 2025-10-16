<?php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Models\News;
use App\Domains\DeveloperWeb\Repositories\NewsRepository;
use App\Domains\Administrator\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsService
{
    public function __construct(
        private NewsRepository $newsRepository
    ) {}

    public function getAllNews(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->newsRepository->getAllPaginated($perPage, $filters);
    }

    public function getNewsById(int $id): ?News
    {
        return $this->newsRepository->findById($id);
    }

    public function getNewsBySlug(string $slug): ?News
    {
        return $this->newsRepository->findBySlug($slug);
    }

    public function createNews(array $data): News
    {
        // Generar slug si no se proporciona
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        // Obtener el ID del autor
        $authorId = $this->getCurrentUserId();

        // Si no hay usuario autenticado, buscar un usuario por defecto
        if (!$authorId) {
            $authorId = $this->getDefaultUserId();
        }

        // Asegurar que los tags sean un array válido y convertirlos a JSON
        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            try {
                $tags = json_decode($tags, true) ?? [];
            } catch (\Exception $e) {
                $tags = [];
            }
        }
        
        // Si es un array, asegurarse de que solo contenga strings
        if (is_array($tags)) {
            $tags = array_filter($tags, function($tag) {
                return is_string($tag) && !empty(trim($tag));
            });
            $tags = array_values($tags); // Reindexar array
        } else {
            $tags = [];
        }

        $validatedData = [
            'id_news' => $this->newsRepository->getNextNewsId(),
            'title' => $data['title'],
            'slug' => $data['slug'],
            'summary' => $data['summary'],
            'content' => $data['content'],
            'featured_image' => $data['featured_image'] ?? null,
            'author_id' => $authorId,
            'category' => $data['category'] ?? null,
            'tags' => !empty($tags) ? json_encode($tags) : null, // Convertir a JSON string
            'status' => $data['status'],
            'views' => 0,
            'published_date' => $data['published_date'] ?? ($data['status'] === 'published' ? now() : null),
            'created_date' => now(),
            'updated_date' => now(),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
        ];

        return $this->newsRepository->create($validatedData);
    }

    public function updateNews(int $id, array $data): bool
    {
        $news = $this->newsRepository->findById($id);

        if (!$news) {
            return false;
        }

        // Generar nuevo slug si el título cambió
        if (isset($data['title']) && $data['title'] !== $news->title) {
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $news->id);
            }
        }

        // Procesar tags para update también
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            if (is_string($tags)) {
                try {
                    $tags = json_decode($tags, true) ?? [];
                } catch (\Exception $e) {
                    $tags = [];
                }
            }
            
            if (is_array($tags)) {
                $tags = array_filter($tags, function($tag) {
                    return is_string($tag) && !empty(trim($tag));
                });
                $tags = array_values($tags);
                $data['tags'] = !empty($tags) ? json_encode($tags) : null;
            } else {
                $data['tags'] = null;
            }
        }

        return $this->newsRepository->update($news, $data);
    }

    public function deleteNews(int $id): bool
    {
        $news = $this->newsRepository->findById($id);

        if (!$news) {
            return false;
        }

        return $this->newsRepository->delete($news);
    }

    public function incrementViews(int $id): bool
    {
        $news = $this->newsRepository->findById($id);

        if (!$news) {
            return false;
        }

        return $this->newsRepository->incrementViews($news);
    }

    public function getStatusCounts(): array
    {
        return $this->newsRepository->getStatusCounts();
    }

    public function getCategoryCounts(): array
    {
        return $this->newsRepository->getCategoryCounts();
    }

    public function getPublishedNews()
    {
        return $this->newsRepository->getPublishedNews();
    }

    public function getRelatedNews(int $id): array
    {
        $news = $this->newsRepository->findById($id);
        
        if (!$news) {
            return [];
        }

        return $this->newsRepository->getRelatedNews($news)->toArray();
    }

    public function getNewsForPublic(array $filters = []): array
    {
        $filters['published_only'] = true;
        return $this->newsRepository->getAllPaginated(10, $filters)->items();
    }

    /**
     * Generar un slug único para la noticia
     */
    private function generateUniqueSlug(string $title, int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si un slug ya existe
     */
    private function slugExists(string $slug, int $excludeId = null): bool
    {
        $query = News::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Obtener el ID del usuario actualmente autenticado
     */
    private function getCurrentUserId(): ?int
    {
        // Temporalmente devolvemos null hasta que tengas autenticación
        return null;

        // Cuando tengas autenticación, descomenta esto:
        // return auth()->id();
    }

    /**
     * Obtener un ID de usuario por defecto
     */
    private function getDefaultUserId(): int
    {
        try {
            // Buscar cualquier usuario existente
            $user = User::first();

            if ($user) {
                return $user->id;
            }

            // Si no hay usuarios, crear uno temporal
            return $this->createTemporaryUser();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuario por defecto para noticias', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('No hay usuarios disponibles en el sistema. Por favor, crea al menos un usuario primero.');
        }
    }

    /**
     * Crear un usuario temporal para desarrollo
     */
    private function createTemporaryUser(): int
    {
        try {
            $user = User::create([
                'first_name' => 'System',
                'last_name' => 'News',
                'full_name' => 'System News',
                'email' => 'news@incadev.com',
                'password' => bcrypt('temporary_password'),
                'role' => json_encode(['admin']),
                'status' => 'active',
            ]);

            Log::info('Usuario temporal creado para news', ['user_id' => $user->id]);

            return $user->id;
        } catch (\Exception $e) {
            Log::error('Error al crear usuario temporal para news', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('No se pudo crear un usuario temporal. Error: ' . $e->getMessage());
        }
    }
}