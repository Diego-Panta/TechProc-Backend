<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    /**
     * Register any application services.
     */
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $modules = [
            'Administrator',
            'DataAnalyst',
            'DeveloperWeb',
            'Lms',
            'SupportInfrastructure',
            'SupportSecurity',
            'SupportTechnical',
            'AuthenticationSessions',
        ];

        foreach ($modules as $module) {
            $path = base_path("app/Domains/{$module}/routes.php");
            if (file_exists($path)) {
                require $path;
            }
        }
    }
}