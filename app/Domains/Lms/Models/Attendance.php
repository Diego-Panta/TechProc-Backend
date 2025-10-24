<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    protected $primaryKey = 'id';

    protected $fillable = [
        'group_participant_id',
        'class_id',
        'attended',
        'entry_time',
        'exit_time',
        'connected_minutes',
        'connection_ip',
        'device',
        'approximate_location',
        'connection_quality',
        'observations',
        'cloud_synchronized',
        'record_date',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'connected_minutes' => 'integer',
        'cloud_synchronized' => 'boolean',
        'record_date' => 'datetime',
    ];

    public function groupParticipant()
    {
        return $this->belongsTo(GroupParticipant::class, 'group_participant_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}