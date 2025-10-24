<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;

class TeacherProfile extends Model
{
    use HasFactory;

    protected $table = 'teacher_profiles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'professional_title',
        'specialty',
        'experience_years',
        'biography',
        'linkedin_link',
        'cover_photo',
    ];

    protected $casts = [
        'experience_years' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}