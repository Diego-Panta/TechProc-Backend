<?php

namespace Database\Seeders;

use App\Domains\DeveloperWeb\Models\ContentItem;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Domains\DeveloperWeb\Enums\NewsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando contenido de prueba...');

        $contentItems = [
            // NEWS 1 - Tecnología
            [
                'content_type' => ContentType::NEWS->value,
                'title' => 'Nueva plataforma de aprendizaje en línea revoluciona la educación',
                'slug' => 'nueva-plataforma-aprendizaje-revoluciona-educacion',
                'content' => 'Una nueva era en la educación digital

La plataforma Incadev presenta su nuevo sistema de gestión de aprendizaje que integra inteligencia artificial para personalizar la experiencia educativa de cada estudiante.

Características principales:

- Análisis predictivo de deserción estudiantil
- Chatbot inteligente con IA para soporte 24/7
- Dashboard interactivo para estudiantes y profesores
- Sistema de evaluación automática

Esta innovación marca un antes y un después en cómo se imparte la educación en línea.',
                'summary' => 'Incadev lanza su nueva plataforma educativa con IA integrada que promete transformar la experiencia de aprendizaje.',
                'image_url' => 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?w=1200&h=800&fit=crop',
                'status' => ContentStatus::PUBLISHED->value,
                'views' => 1234,
                'priority' => 1,
                'published_date' => now()->subDays(2),
                'category' => NewsCategory::EDUCATION->value,
                'seo_title' => 'Nueva Plataforma Educativa con IA - Incadev',
                'seo_description' => 'Descubre cómo la nueva plataforma de Incadev está revolucionando la educación en línea con inteligencia artificial.',
                'metadata' => [
                    'author' => 'Equipo Incadev',
                    'reading_time' => '3 min',
                    'tags' => ['educación', 'tecnología', 'IA', 'e-learning']
                ],
            ],

            // NEWS 2 - Tecnología
            [
                'content_type' => ContentType::NEWS->value,
                'title' => 'Inteligencia Artificial en la educación: El futuro es ahora',
                'slug' => 'inteligencia-artificial-educacion-futuro',
                'content' => 'La IA transforma el aula

Los sistemas de inteligencia artificial están cambiando radicalmente la forma en que los estudiantes aprenden y los profesores enseñan.

Desde tutores virtuales hasta sistemas de evaluación automática, la IA está democratizando el acceso a educación de calidad.

"La tecnología no reemplaza al profesor, lo potencia" - Dr. Juan Pérez, experto en EdTech

El impacto de estas tecnologías se refleja en mejores tasas de retención estudiantil y mayor satisfacción tanto de alumnos como de docentes.',
                'summary' => 'La inteligencia artificial está revolucionando la educación con soluciones innovadoras para estudiantes y profesores.',
                'image_url' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=1200&h=800&fit=crop',
                'status' => ContentStatus::PUBLISHED->value,
                'views' => 856,
                'priority' => 2,
                'published_date' => now()->subDays(5),
                'category' => NewsCategory::TECHNOLOGY->value,
                'seo_title' => 'IA en la Educación: Transformando el Aprendizaje',
                'seo_description' => 'Conoce cómo la inteligencia artificial está revolucionando la educación moderna.',
                'metadata' => [
                    'author' => 'María González',
                    'reading_time' => '5 min',
                    'tags' => ['IA', 'educación', 'tecnología', 'futuro']
                ],
            ],

            // NEWS 3 - Educación
            [
                'content_type' => ContentType::NEWS->value,
                'title' => 'Mejores prácticas para el aprendizaje en línea exitoso',
                'slug' => 'mejores-practicas-aprendizaje-en-linea',
                'content' => 'Consejos para maximizar tu experiencia de aprendizaje

El aprendizaje en línea requiere disciplina y estrategia. Aquí te compartimos las mejores prácticas:

1. Crea un espacio dedicado para estudiar
2. Establece un horario regular
3. Participa activamente en foros y discusiones
4. Toma descansos regulares
5. Conecta con tus compañeros

Siguiendo estos consejos, podrás aprovechar al máximo tu experiencia educativa en línea. La clave está en la constancia y en mantener una rutina que te permita balancear tus estudios con otras actividades.',
                'summary' => 'Descubre las estrategias comprobadas para tener éxito en tu educación en línea.',
                'image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200&h=800&fit=crop',
                'status' => ContentStatus::PUBLISHED->value,
                'views' => 645,
                'priority' => 3,
                'published_date' => now()->subWeek(),
                'category' => NewsCategory::EDUCATION->value,
                'seo_title' => 'Guía Completa para el Aprendizaje en Línea Exitoso',
                'seo_description' => 'Aprende las mejores prácticas y estrategias para destacar en tu educación en línea.',
                'metadata' => [
                    'author' => 'Carlos Rodríguez',
                    'reading_time' => '4 min',
                    'tags' => ['educación', 'tips', 'e-learning', 'estudiantes']
                ],
            ],

            // NEWS 4 - Negocios
            [
                'content_type' => ContentType::NEWS->value,
                'title' => 'Incadev alcanza 10,000 estudiantes activos',
                'slug' => 'incadev-10000-estudiantes-activos',
                'content' => 'Un hito importante para nuestra comunidad

Estamos orgullosos de anunciar que hemos alcanzado los 10,000 estudiantes activos en nuestra plataforma.

Este logro representa el compromiso de nuestra comunidad con la educación de calidad y el aprendizaje continuo.

Gracias a todos nuestros estudiantes, profesores y colaboradores por hacer esto posible. Este es solo el comienzo de un camino que esperamos nos lleve a impactar positivamente la vida de miles de personas más.',
                'summary' => 'Incadev celebra un hito importante al alcanzar 10,000 estudiantes activos en su plataforma.',
                'image_url' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1200&h=800&fit=crop',
                'status' => ContentStatus::PUBLISHED->value,
                'views' => 2341,
                'priority' => 1,
                'published_date' => now()->subDays(1),
                'category' => NewsCategory::BUSINESS->value,
                'seo_title' => 'Incadev Alcanza 10,000 Estudiantes Activos',
                'seo_description' => 'La plataforma educativa Incadev celebra un importante hito con 10,000 estudiantes activos.',
                'metadata' => [
                    'author' => 'Equipo Incadev',
                    'reading_time' => '2 min',
                    'tags' => ['hito', 'comunidad', 'logro', 'crecimiento']
                ],
            ],

            // ANNOUNCEMENT 1
            [
                'content_type' => ContentType::ANNOUNCEMENT->value,
                'title' => 'Nuevos cursos disponibles en Programación Web',
                'slug' => 'nuevos-cursos-programacion-web',
                'content' => 'Nos complace anunciar que hemos agregado 5 nuevos cursos de programación web a nuestro catálogo:

- React Avanzado
- Node.js y Express
- Vue.js 3 desde cero
- TypeScript para desarrolladores
- Full Stack Developer Bootcamp

Las inscripciones están abiertas. ¡No te pierdas esta oportunidad de potenciar tus habilidades como desarrollador web!',
                'summary' => 'Inscríbete ahora en nuestros nuevos cursos de programación web y lleva tus habilidades al siguiente nivel.',
                'image_url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&h=800&fit=crop',
                'status' => ContentStatus::ACTIVE->value,
                'views' => 523,
                'priority' => 1,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addWeeks(2),
                'link_url' => '/courses',
                'link_text' => 'Ver cursos disponibles',
                'button_text' => 'Inscribirme ahora',
                'metadata' => [
                    'background_color' => '#4F46E5',
                    'text_color' => '#FFFFFF'
                ],
            ],

            // ANNOUNCEMENT 2
            [
                'content_type' => ContentType::ANNOUNCEMENT->value,
                'title' => 'Mantenimiento programado del sistema',
                'slug' => 'mantenimiento-programado-sistema',
                'content' => 'Informamos que realizaremos un mantenimiento programado de nuestros servidores el próximo domingo 25 de noviembre de 2:00 AM a 6:00 AM.

Durante este periodo, la plataforma no estará disponible.

Pedimos disculpas por las molestias y agradecemos su comprensión. Este mantenimiento nos permitirá mejorar el rendimiento y la seguridad de la plataforma.',
                'summary' => 'Mantenimiento del sistema el domingo 25 de noviembre de 2:00 AM a 6:00 AM.',
                'image_url' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1200&h=800&fit=crop',
                'status' => ContentStatus::ACTIVE->value,
                'views' => 892,
                'priority' => 2,
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(5),
                'link_url' => '/support',
                'link_text' => 'Más información',
                'button_text' => 'Contactar soporte',
                'metadata' => [
                    'background_color' => '#F59E0B',
                    'text_color' => '#000000'
                ],
            ],

            // ALERT 1
            [
                'content_type' => ContentType::ALERT->value,
                'title' => 'Actualiza tu perfil para mejorar tu experiencia',
                'slug' => 'actualiza-perfil-mejora-experiencia',
                'content' => 'Hemos detectado que tu perfil está incompleto. Actualiza tu información para obtener recomendaciones personalizadas de cursos y una mejor experiencia en la plataforma.',
                'summary' => 'Completa tu perfil para recibir recomendaciones personalizadas.',
                'image_url' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=1200&h=800&fit=crop',
                'status' => ContentStatus::ACTIVE->value,
                'views' => 234,
                'priority' => 3,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'item_type' => 'info',
                'link_url' => '/profile/edit',
                'link_text' => 'Actualizar perfil',
                'button_text' => 'Ir a mi perfil',
                'metadata' => [
                    'dismissible' => true,
                    'icon' => 'info'
                ],
            ],

            // ALERT 2
            [
                'content_type' => ContentType::ALERT->value,
                'title' => '¡Últimos días para inscribirte con descuento!',
                'slug' => 'ultimos-dias-inscripcion-descuento',
                'content' => 'Aprovecha el 30% de descuento en todos nuestros cursos. La promoción termina el 30 de noviembre.

¡No dejes pasar esta oportunidad de invertir en tu educación!',
                'summary' => '30% de descuento en todos los cursos hasta el 30 de noviembre.',
                'image_url' => 'https://images.unsplash.com/photo-1607703703674-df96af81dffa?w=1200&h=800&fit=crop',
                'status' => ContentStatus::ACTIVE->value,
                'views' => 1567,
                'priority' => 1,
                'start_date' => now()->subWeek(),
                'end_date' => now()->addWeek(),
                'item_type' => 'warning',
                'link_url' => '/courses?promo=true',
                'link_text' => 'Ver cursos en promoción',
                'button_text' => 'Aprovechar descuento',
                'metadata' => [
                    'dismissible' => false,
                    'icon' => 'megaphone',
                    'promo_code' => 'PROMO30'
                ],
            ],

            // NEWS 5 - Ciencia
            [
                'content_type' => ContentType::NEWS->value,
                'title' => 'El aprendizaje adaptativo: ciencia detrás de la educación personalizada',
                'slug' => 'aprendizaje-adaptativo-ciencia-educacion',
                'content' => 'La neurociencia aplicada a la educación

Estudios recientes demuestran que el aprendizaje adaptativo puede mejorar la retención de conocimiento hasta en un 60%.

La clave está en ajustar el contenido y el ritmo de aprendizaje según las necesidades individuales de cada estudiante.

Beneficios comprobados:

- Mayor retención de información
- Reducción del estrés académico
- Mejor rendimiento en evaluaciones
- Mayor satisfacción estudiantil

Los investigadores coinciden en que este enfoque representa el futuro de la educación personalizada.',
                'summary' => 'Investigaciones científicas revelan los beneficios del aprendizaje adaptativo en la educación moderna.',
                'image_url' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=1200&h=800&fit=crop',
                'status' => ContentStatus::PUBLISHED->value,
                'views' => 423,
                'priority' => 4,
                'published_date' => now()->subDays(10),
                'category' => NewsCategory::SCIENCE->value,
                'seo_title' => 'Aprendizaje Adaptativo: La Ciencia de la Educación Personalizada',
                'seo_description' => 'Descubre cómo la ciencia respalda el aprendizaje adaptativo y sus beneficios comprobados.',
                'metadata' => [
                    'author' => 'Dr. Ana Martínez',
                    'reading_time' => '6 min',
                    'tags' => ['ciencia', 'neurociencia', 'aprendizaje', 'investigación']
                ],
            ],

            // NEWS 6 - Salud
            [
                'content_type' => ContentType::NEWS->value,
                'title' => 'Salud mental y educación en línea: Consejos para estudiantes',
                'slug' => 'salud-mental-educacion-en-linea',
                'content' => 'Cuida tu bienestar mientras estudias

La educación en línea trae muchos beneficios, pero también nuevos desafíos para la salud mental de los estudiantes.

Estrategias para mantener el equilibrio:

1. Establece límites entre estudio y tiempo personal
2. Mantén contacto social con compañeros
3. Practica ejercicio regularmente
4. Duerme suficiente
5. No dudes en pedir ayuda si la necesitas

Recuerda: tu salud mental es tan importante como tu rendimiento académico. Cuidarte a ti mismo es parte fundamental del proceso de aprendizaje.',
                'summary' => 'Consejos prácticos para cuidar tu salud mental mientras estudias en línea.',
                'image_url' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=1200&h=800&fit=crop',
                'status' => ContentStatus::PUBLISHED->value,
                'views' => 789,
                'priority' => 2,
                'published_date' => now()->subDays(4),
                'category' => NewsCategory::HEALTH->value,
                'seo_title' => 'Salud Mental en la Educación en Línea: Guía Práctica',
                'seo_description' => 'Aprende a cuidar tu salud mental mientras estudias en línea con estos consejos prácticos.',
                'metadata' => [
                    'author' => 'Lic. Patricia Vega',
                    'reading_time' => '4 min',
                    'tags' => ['salud mental', 'bienestar', 'estudiantes', 'consejos']
                ],
            ],
        ];

        foreach ($contentItems as $item) {
            ContentItem::create($item);
        }

        $this->command->info('✅ 10 items de contenido creados exitosamente:');
        $this->command->info('   - 6 Noticias (NEWS)');
        $this->command->info('   - 2 Anuncios (ANNOUNCEMENT)');
        $this->command->info('   - 2 Alertas (ALERT)');
    }
}
