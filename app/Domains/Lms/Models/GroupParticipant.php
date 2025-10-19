<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;

class GroupParticipant extends Model
{
    use HasFactory;

    protected $table = 'group_participants';
    protected $primaryKey = 'id';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'teacher_function',
        'enrollment_status',
        'assignment_date',
        'schedule',
    ];

    protected $casts = [
        'assignment_date' => 'datetime',
        'schedule' => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'group_participant_id');
    }
}