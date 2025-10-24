<?php

namespace App\Domains\Lms\Models;

use App\Domains\Administrator\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';
    protected $primaryKey = 'id';
    
    // Deshabilitar timestamps automáticos (updated_at no existe en la tabla)
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'user_id',
        'company_id',
        'document_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }
}