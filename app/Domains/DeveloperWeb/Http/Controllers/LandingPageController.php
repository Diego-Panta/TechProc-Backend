<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Models\ContentItem;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use IncadevUns\CoreDomain\Models\Course;
use IncadevUns\CoreDomain\Models\CourseVersion;
use IncadevUns\CoreDomain\Models\TeacherProfile;
use IncadevUns\CoreDomain\Models\SurveyResponse;
use IncadevUns\CoreDomain\Enums\CourseVersionStatus;

class LandingPageController extends Controller
{
    /**
     * 1. Obtener estadísticas para el Hero Section
     *
     * GET /api/developer-web/landing/hero-stats
     */
    public function getHeroStats(): JsonResponse
    {
        try {
            // Contar estudiantes (usuarios con rol 'student')
            $studentsCount = User::role('student')->count();

            // Contar cursos activos
            $coursesCount = Course::count();

            // Contar docentes (usuarios con rol 'teacher' o que tengan perfil de docente)
            $teachersCount = User::role('teacher')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $studentsCount,
                    'courses' => $coursesCount,
                    'teachers' => $teachersCount,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 2. Obtener cursos disponibles para mostrar en cards
     *
     * GET /api/developer-web/landing/courses
     */
    public function getAvailableCourses(): JsonResponse
    {
        try {
            // Obtener cursos que tengan al menos una versión publicada
            $courses = Course::whereHas('versions', function ($query) {
                    $query->where('status', CourseVersionStatus::Published->value);
                })
                ->with(['versions' => function ($query) {
                    $query->where('status', CourseVersionStatus::Published->value)
                          ->orderBy('created_at', 'desc');
                }])
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($course) {
                    // Obtener la versión publicada más reciente
                    $publishedVersion = $course->versions->first();

                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'description' => $course->description ?? 'Sin descripción disponible',
                        'image' => $course->image_path ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=600&fit=crop',
                        'version' => $publishedVersion?->version,
                        'version_name' => $publishedVersion?->name,
                        'price' => $publishedVersion?->price,
                        'created_at' => $course->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $courses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cursos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 3. Obtener profesores destacados
     *
     * GET /api/developer-web/landing/featured-teachers
     */
    public function getFeaturedTeachers(): JsonResponse
    {
        try {
            // Obtener los últimos docentes con su información básica
            $teachers = User::role('teacher')
                ->orderBy('created_at', 'desc')
                ->limit(4) // Mostrar los 4 profesores más recientes
                ->get()
                ->map(function ($teacher) {
                    // Intentar obtener el perfil de profesor desde el paquete
                    $teacherProfile = null;
                    try {
                        $teacherProfile = TeacherProfile::where('user_id', $teacher->id)->first();
                    } catch (\Exception $e) {
                        // Si falla, continuar sin perfil
                    }

                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->fullname ?? $teacher->name,
                        'avatar' => $teacher->avatar,
                        'subject_areas' => $teacherProfile?->subject_areas ?? null,
                        'professional_summary' => $teacherProfile?->professional_summary ?? null,
                        'cv_path' => $teacherProfile?->cv_path ?? null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $teachers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener profesores',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 4. Obtener testimonios de estudiantes (respuestas a encuestas)
     *
     * GET /api/developer-web/landing/testimonials
     */
    public function getTestimonials(): JsonResponse
    {
        try {
            // Intentar obtener testimonios de encuestas
            $testimonials = collect();

            try {
                $surveyResponses = SurveyResponse::orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                foreach ($surveyResponses as $response) {
                    try {
                        $user = User::find($response->user_id);
                        if (!$user) continue;

                        // Buscar detalles con texto
                        $details = DB::table('response_details')
                            ->where('survey_response_id', $response->id)
                            ->whereNotNull('text_response')
                            ->where('text_response', '!=', '')
                            ->first();

                        if ($details && $details->text_response) {
                            $testimonials->push([
                                'id' => $response->id,
                                'student_name' => $user->fullname ?? $user->name,
                                'student_avatar' => $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200',
                                'comment' => $details->text_response,
                                'rating' => $details->numeric_response ?? null,
                                'date' => $response->created_at->format('Y-m-d'),
                            ]);
                        }

                        if ($testimonials->count() >= 6) break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            } catch (\Exception $e) {
                // Si no hay testimonios de encuestas, devolver array vacío
            }

            return response()->json([
                'success' => true,
                'data' => $testimonials->take(6)->values()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener testimonios',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 5. Obtener noticias públicas de la tabla content_items
     *
     * GET /api/developer-web/landing/news
     */
    public function getPublicNews(): JsonResponse
    {
        try {
            $news = ContentItem::where('content_type', ContentType::NEWS->value)
                ->where('status', ContentStatus::PUBLISHED->value)
                ->where(function ($query) {
                    // Contenido activo en fechas
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->orderBy('published_date', 'desc')
                ->limit(6) // Últimas 6 noticias
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'summary' => $item->summary,
                        'image_url' => $item->image_url,
                        'category' => $item->category,
                        'published_date' => $item->published_date?->format('Y-m-d'),
                        'views' => $item->views,
                        'reading_time' => $item->metadata['reading_time'] ?? null,
                        'author' => $item->metadata['author'] ?? null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $news
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener noticias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener detalle completo de una noticia
     *
     * GET /api/developer-web/landing/news/{id}
     */
    public function getNewsDetail(int $id): JsonResponse
    {
        try {
            $news = ContentItem::where('content_type', ContentType::NEWS->value)
                ->where('id', $id)
                ->where('status', ContentStatus::PUBLISHED->value)
                ->first();

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            // Incrementar vistas
            $news->incrementViews();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $news->id,
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'content' => $news->content,
                    'summary' => $news->summary,
                    'image_url' => $news->image_url,
                    'category' => $news->category,
                    'published_date' => $news->published_date?->format('Y-m-d H:i:s'),
                    'views' => $news->views,
                    'metadata' => $news->metadata,
                    'seo_title' => $news->seo_title,
                    'seo_description' => $news->seo_description,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener anuncios activos para mostrar en la landing
     *
     * GET /api/developer-web/landing/announcements
     */
    public function getActiveAnnouncements(): JsonResponse
    {
        try {
            $announcements = ContentItem::where('content_type', ContentType::ANNOUNCEMENT->value)
                ->where('status', ContentStatus::ACTIVE->value)
                ->where(function ($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->orderBy('priority', 'asc')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'content' => $item->content,
                        'summary' => $item->summary,
                        'image_url' => $item->image_url,
                        'link_url' => $item->link_url,
                        'button_text' => $item->button_text,
                        'priority' => $item->priority,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $announcements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener anuncios',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener alertas activas
     *
     * GET /api/developer-web/landing/alerts
     */
    public function getActiveAlerts(): JsonResponse
    {
        try {
            $alerts = ContentItem::where('content_type', ContentType::ALERT->value)
                ->where('status', ContentStatus::ACTIVE->value)
                ->where(function ($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->orderBy('priority', 'asc')
                ->limit(2)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'content' => $item->content,
                        'item_type' => $item->item_type, // info, warning, error
                        'link_url' => $item->link_url,
                        'button_text' => $item->button_text,
                        'dismissible' => $item->metadata['dismissible'] ?? true,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener alertas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Helper: Extraer rating de las respuestas si existe
     */
    private function extractRating($response): ?int
    {
        // Buscar en las respuestas si hay alguna con valor numérico que parezca un rating
        foreach ($response->responseDetails as $detail) {
            if ($detail->numeric_response && $detail->numeric_response >= 1 && $detail->numeric_response <= 5) {
                return (int) $detail->numeric_response;
            }
        }
        return null;
    }
}
