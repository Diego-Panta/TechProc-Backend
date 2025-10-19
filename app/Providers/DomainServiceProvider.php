<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\DeveloperWeb\Repositories\ContactFormRepository;
use App\Domains\DeveloperWeb\Services\ContactFormService;
use App\Domains\DataAnalyst\Repositories\StudentReportRepository;
use App\Domains\DataAnalyst\Services\StudentReportService;

// LMS Repositories
use App\Domains\Lms\Repositories\CourseRepositoryInterface;
use App\Domains\Lms\Repositories\CourseRepository;
use App\Domains\Lms\Repositories\StudentRepositoryInterface;
use App\Domains\Lms\Repositories\StudentRepository;
use App\Domains\Lms\Repositories\InstructorRepositoryInterface;
use App\Domains\Lms\Repositories\InstructorRepository;
use App\Domains\Lms\Repositories\CategoryRepositoryInterface;
use App\Domains\Lms\Repositories\CategoryRepository;
use App\Domains\Lms\Repositories\EnrollmentRepositoryInterface;
use App\Domains\Lms\Repositories\EnrollmentRepository;

// SupportTechnical Repositories
use App\Domains\SupportTechnical\Repositories\TicketRepositoryInterface;
use App\Domains\SupportTechnical\Repositories\TicketRepository;
use App\Domains\SupportTechnical\Repositories\EscalationRepositoryInterface;
use App\Domains\SupportTechnical\Repositories\EscalationRepository;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar bindings de LMS
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(InstructorRepositoryInterface::class, InstructorRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(EnrollmentRepositoryInterface::class, EnrollmentRepository::class);

        // Registrar bindings de SupportTechnical
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(EscalationRepositoryInterface::class, EscalationRepository::class);
        
        // Registrar bindings para DeveloperWeb
        $this->app->bind(ContactFormRepository::class, function ($app) {
            return new ContactFormRepository();
        });

        $this->app->bind(ContactFormService::class, function ($app) {
            return new ContactFormService(
                $app->make(ContactFormRepository::class)
            );
        });

        // Registrar bindings para DataAnalyst
        /*$this->app->bind(StudentReportRepository::class, function ($app) {
            return new StudentReportRepository();
        });

        $this->app->bind(StudentReportService::class, function ($app) {
            return new StudentReportService(
                $app->make(StudentReportRepository::class)
            );
        });*/
    }
    
    public function boot()
    {
        $modules = [
            'Administrator',
            'DataAnalyst',
            'DeveloperWeb',
            'LMS',
            'SupportInfrastructure',
            'SupportSecurity',
            'SupportTechnical',
            'AuthenticationSessions'
        ];

        foreach ($modules as $module) {
            // Cargar rutas web
            $webPath = base_path("app/Domains/{$module}/routes.php");
            if (file_exists($webPath)) {
                require $webPath;
            }

            // Cargar rutas API
            $apiPath = base_path("app/Domains/{$module}/api.php");
            if (file_exists($apiPath)) {
                require $apiPath;
            }
        }
    }
}