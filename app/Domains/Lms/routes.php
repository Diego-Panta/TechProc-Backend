<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Lms\Http\Controllers\CourseController;
use App\Domains\Lms\Http\Controllers\StudentController;
use App\Domains\Lms\Http\Controllers\InstructorController;
use App\Domains\Lms\Http\Controllers\CategoryController;
use App\Domains\Lms\Http\Controllers\EnrollmentController;
use App\Domains\Lms\Http\Controllers\CompanyController;
use App\Domains\Lms\Http\Controllers\AcademicPeriodController;

// TODO: Agregar middleware de autenticación cuando esté disponible
// Route::middleware(['auth:api'])->group(function () {

Route::prefix('lms')->group(function () {
    
    // Gestión de Cursos
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course_id}', [CourseController::class, 'show']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{course_id}', [CourseController::class, 'update']);
    Route::delete('/courses/{course_id}', [CourseController::class, 'destroy']);
    
    // Gestión de Estudiantes
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{student_id}', [StudentController::class, 'show']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::put('/students/{student_id}', [StudentController::class, 'update']);
    Route::delete('/students/{student_id}', [StudentController::class, 'destroy']);
    
    // Gestión de Instructores
    Route::get('/instructors', [InstructorController::class, 'index']);
    Route::post('/instructors', [InstructorController::class, 'store']);
    Route::put('/instructors/{instructor_id}', [InstructorController::class, 'update']);
    
    // Categorías de Cursos
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category_id}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category_id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category_id}', [CategoryController::class, 'destroy']);
    
    // Matrículas (Enrollments)
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/enrollments', [EnrollmentController::class, 'store']);

    // Gestión de Empresas
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/companies/{company_id}', [CompanyController::class, 'show']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::put('/companies/{company_id}', [CompanyController::class, 'update']);
    Route::delete('/companies/{company_id}', [CompanyController::class, 'destroy']);

    // Gestión de Periodos Académicos
    Route::get('/academic-periods', [AcademicPeriodController::class, 'index']);
    Route::get('/academic-periods/{academic_period_id}', [AcademicPeriodController::class, 'show']);
    Route::post('/academic-periods', [AcademicPeriodController::class, 'store']);
    Route::put('/academic-periods/{academic_period_id}', [AcademicPeriodController::class, 'update']);
    Route::delete('/academic-periods/{academic_period_id}', [AcademicPeriodController::class, 'destroy']);

});

// });


