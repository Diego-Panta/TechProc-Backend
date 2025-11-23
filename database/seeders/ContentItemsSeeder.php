<?php

namespace Database\Seeders;

use App\Domains\DeveloperWeb\Models\ContentItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentItemsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $contentItems = [
            // NEWS - 2 registros published
            [
                'content_type' => 'news',
                'title' => 'Nueva plataforma de aprendizaje disponible',
                'slug' => 'nueva-plataforma-aprendizaje-disponible',
                'content' => '<p>Estamos emocionados de anunciar el lanzamiento de nuestra nueva plataforma de aprendizaje en línea. Esta plataforma ofrece cursos interactivos, evaluaciones en tiempo real y certificaciones reconocidas.</p><p>Los estudiantes podrán acceder a contenido de alta calidad desde cualquier dispositivo.</p>',
                'summary' => 'Lanzamos nuestra nueva plataforma de aprendizaje con cursos interactivos y certificaciones.',
                'image_url' => 'https://picsum.photos/seed/news1/800/400',
                'status' => 'published',
                'views' => 150,
                'priority' => 1,
                'published_date' => $now->copy()->subDays(5),
                'category' => 'tecnologia',
                'item_type' => 'article',
                'seo_title' => 'Nueva Plataforma de Aprendizaje - TechProc',
                'seo_description' => 'Descubre nuestra nueva plataforma de aprendizaje en línea con cursos interactivos.',
                'metadata' => json_encode(['tags' => ['educación', 'tecnología', 'plataforma'], 'read_time' => '3 min']),
            ],
            [
                'content_type' => 'news',
                'title' => 'Resultados del programa de becas 2025',
                'slug' => 'resultados-programa-becas-2025',
                'content' => '<p>Nos complace anunciar los resultados del programa de becas 2025. Este año hemos otorgado más de 100 becas a estudiantes destacados de diversas regiones del país.</p><p>Felicitamos a todos los beneficiarios y agradecemos a quienes participaron en el proceso de selección.</p>',
                'summary' => 'Anunciamos los resultados del programa de becas con más de 100 beneficiarios.',
                'image_url' => 'https://picsum.photos/seed/news2/800/400',
                'status' => 'published',
                'views' => 320,
                'priority' => 2,
                'published_date' => $now->copy()->subDays(2),
                'category' => 'institucional',
                'item_type' => 'article',
                'seo_title' => 'Resultados Becas 2025 - TechProc',
                'seo_description' => 'Conoce los resultados del programa de becas 2025 y los beneficiarios seleccionados.',
                'metadata' => json_encode(['tags' => ['becas', 'educación', 'oportunidades'], 'read_time' => '2 min']),
            ],

            // ANNOUNCEMENT - 2 registros active
            [
                'content_type' => 'announcement',
                'title' => 'Inscripciones abiertas para el semestre 2025-I',
                'slug' => 'inscripciones-abiertas-semestre-2025-i',
                'content' => '<p>Las inscripciones para el semestre 2025-I ya están abiertas. No pierdas la oportunidad de ser parte de nuestra comunidad educativa.</p>',
                'summary' => 'Inscríbete ahora para el nuevo semestre académico.',
                'image_url' => 'https://picsum.photos/seed/announcement1/800/400',
                'status' => 'active',
                'views' => 89,
                'priority' => 1,
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addDays(30),
                'category' => 'academico',
                'item_type' => 'general',
                'target_page' => 'home',
                'link_url' => '/inscripciones',
                'link_text' => 'Inscríbete aquí',
                'button_text' => 'Inscribirse',
                'metadata' => json_encode(['featured' => true]),
            ],
            [
                'content_type' => 'announcement',
                'title' => 'Nuevo horario de atención administrativa',
                'slug' => 'nuevo-horario-atencion-administrativa',
                'content' => '<p>A partir del 1 de febrero, el área administrativa atenderá en horario extendido de lunes a viernes de 8:00 AM a 6:00 PM.</p>',
                'summary' => 'Horario extendido de atención administrativa.',
                'image_url' => null,
                'status' => 'active',
                'views' => 45,
                'priority' => 2,
                'start_date' => $now->copy()->subDays(3),
                'end_date' => $now->copy()->addDays(60),
                'category' => 'administrativo',
                'item_type' => 'general',
                'target_page' => 'all',
                'link_url' => '/contacto',
                'link_text' => 'Más información',
                'button_text' => 'Ver horarios',
                'metadata' => json_encode(['featured' => false]),
            ],

            // ALERT - 2 registros active
            [
                'content_type' => 'alert',
                'title' => 'Mantenimiento programado del sistema',
                'slug' => 'mantenimiento-programado-sistema',
                'content' => '<p>El sistema estará en mantenimiento el domingo 26 de noviembre de 2:00 AM a 6:00 AM. Durante este período, algunos servicios podrían no estar disponibles.</p>',
                'summary' => 'Mantenimiento programado el domingo 26 de noviembre.',
                'image_url' => null,
                'status' => 'active',
                'views' => 210,
                'priority' => 1,
                'start_date' => $now->copy()->subDays(1),
                'end_date' => $now->copy()->addDays(7),
                'category' => null,
                'item_type' => 'warning',
                'target_page' => 'all',
                'link_url' => null,
                'link_text' => null,
                'button_text' => null,
                'metadata' => json_encode(['dismissible' => true]),
            ],
            [
                'content_type' => 'alert',
                'title' => 'Actualización de políticas de privacidad',
                'slug' => 'actualizacion-politicas-privacidad',
                'content' => '<p>Hemos actualizado nuestras políticas de privacidad. Te invitamos a revisarlas para conocer los cambios realizados.</p>',
                'summary' => 'Nuevas políticas de privacidad vigentes.',
                'image_url' => null,
                'status' => 'active',
                'views' => 78,
                'priority' => 2,
                'start_date' => $now->copy()->subDays(5),
                'end_date' => $now->copy()->addDays(30),
                'category' => null,
                'item_type' => 'info',
                'target_page' => 'all',
                'link_url' => '/politicas-privacidad',
                'link_text' => 'Ver políticas',
                'button_text' => 'Leer más',
                'metadata' => json_encode(['dismissible' => true]),
            ],

            // EVENT - 2 registros published
            [
                'content_type' => 'event',
                'title' => 'Conferencia: Inteligencia Artificial en la Educación',
                'slug' => 'conferencia-ia-educacion',
                'content' => '<p>Únete a nuestra conferencia sobre el impacto de la Inteligencia Artificial en la educación moderna. Contaremos con expertos internacionales y sesiones interactivas.</p>',
                'summary' => 'Conferencia sobre IA y educación con expertos internacionales.',
                'image_url' => 'https://picsum.photos/seed/event1/800/400',
                'status' => 'published',
                'views' => 180,
                'priority' => 1,
                'start_date' => $now->copy()->addDays(15),
                'end_date' => $now->copy()->addDays(15),
                'published_date' => $now->copy()->subDays(7),
                'category' => 'academico',
                'item_type' => 'conference',
                'target_page' => 'events',
                'link_url' => '/eventos/conferencia-ia',
                'link_text' => 'Registrarse',
                'button_text' => 'Inscribirse',
                'metadata' => json_encode(['location' => 'Auditorio Principal', 'capacity' => 200, 'tags' => ['IA', 'educación', 'tecnología']]),
            ],
            [
                'content_type' => 'event',
                'title' => 'Taller de Desarrollo Web Full Stack',
                'slug' => 'taller-desarrollo-web-full-stack',
                'content' => '<p>Aprende las últimas tecnologías de desarrollo web en este taller práctico de 3 días. Cubriremos React, Node.js, bases de datos y despliegue en la nube.</p>',
                'summary' => 'Taller práctico de desarrollo web con React y Node.js.',
                'image_url' => 'https://picsum.photos/seed/event2/800/400',
                'status' => 'published',
                'views' => 95,
                'priority' => 2,
                'start_date' => $now->copy()->addDays(30),
                'end_date' => $now->copy()->addDays(32),
                'published_date' => $now->copy()->subDays(3),
                'category' => 'tecnologia',
                'item_type' => 'workshop',
                'target_page' => 'events',
                'link_url' => '/eventos/taller-web',
                'link_text' => 'Más información',
                'button_text' => 'Inscribirse',
                'metadata' => json_encode(['location' => 'Laboratorio de Cómputo', 'capacity' => 30, 'tags' => ['desarrollo', 'web', 'programación']]),
            ],
        ];

        foreach ($contentItems as $item) {
            $slug = $item['slug'];
            unset($item['slug']);

            ContentItem::updateOrCreate(
                ['slug' => $slug],
                $item
            );
        }

        $this->command->info('Se crearon/actualizaron 8 registros en content_items (2 por cada content_type)');
    }
}
