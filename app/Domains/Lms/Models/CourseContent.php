<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model
{
    use HasFactory;

    protected $table = 'course_contents';
    protected $primaryKey = 'id';

    protected $fillable = [
        'course_id',
        'session',
        'type',
        'title',
        'content',
        'order_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}