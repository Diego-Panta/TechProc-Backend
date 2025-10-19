<?php

namespace App\Domains\Administrator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Domains\Lms\Models\Student;
use App\Domains\Lms\Models\Instructor;
use App\Domains\Lms\Models\GroupParticipant;
use App\Domains\SupportTechnical\Models\Ticket;
use App\Domains\Lms\Models\GradeChange;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'dni',
        'document',
        'email',
        'password',
        'phone_number',
        'address',
        'birth_date',
        'role',
        'gender',
        'country',
        'country_location',
        'timezone',
        'profile_photo',
        'status',
        'synchronized',
        'last_access_ip',
        'last_access',
        'last_connection',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'array',
        'birth_date' => 'date',
        'last_access' => 'datetime',
        'last_connection' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'synchronized' => 'boolean',
    ];

    // Relaciones
    public function activeSessions()
    {
        return $this->hasMany(ActiveSession::class, 'user_id');
    }

    public function securityLogs()
    {
        return $this->hasMany(SecurityLog::class, 'user_id');
    }

    public function securityConfigurations()
    {
        return $this->hasMany(SecurityConfiguration::class, 'user_id');
    }

    public function instructor()
    {
        return $this->hasOne(Instructor::class, 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function groupParticipants()
    {
        return $this->hasMany(GroupParticipant::class, 'user_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'teacher_creator_id');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'user_id');
    }

    public function gradings()
    {
        return $this->hasMany(Grading::class, 'teacher_grader_id');
    }

    public function gradeRecords()
    {
        return $this->hasMany(GradeRecord::class, 'user_id');
    }

    public function finalGrades()
    {
        return $this->hasMany(FinalGrade::class, 'user_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'user_id');
    }

    public function diplomas()
    {
        return $this->hasMany(Diploma::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function gradeChanges()
    {
        return $this->hasMany(GradeChange::class, 'user_id');
    }
}
