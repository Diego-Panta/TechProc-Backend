<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'id';

    // La tabla solo tiene created_at, no tiene updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function courseCategories()
    {
        return $this->hasMany(CourseCategory::class, 'category_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_categories', 'category_id', 'course_id');
    }
}