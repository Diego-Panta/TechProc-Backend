<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'group_id',
        'class_name',
        'class_date',
        'start_time',
        'end_time',
        'platform',
        'meeting_url',
        'external_meeting_id',
        'meeting_password',
        'allow_recording',
        'recording_url',
        'max_participants',
        'class_status',
    ];

    protected $casts = [
        'class_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'allow_recording' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }
}