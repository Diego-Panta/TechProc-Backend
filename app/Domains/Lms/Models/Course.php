<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';
    protected $primaryKey = 'id';

    protected $fillable = [
        'course_id',
        'title',
        'name',
        'description',
        'level',
        'course_image',
        'video_url',
        'duration',
        'sessions',
        'selling_price',
        'discount_price',
        'prerequisites',
        'certificate_name',
        'certificate_issuer',
        'bestseller',
        'featured',
        'highest_rated',
        'status',
    ];

    protected $casts = [
        'duration' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'certificate_name' => 'boolean',
        'bestseller' => 'boolean',
        'featured' => 'boolean',
        'highest_rated' => 'boolean',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function courseInstructors()
    {
        return $this->hasMany(CourseInstructor::class, 'course_id');
    }

    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class, 'course_id');
    }

    public function courseCategories()
    {
        return $this->hasMany(CourseCategory::class, 'course_id');
    }

    public function courseContents()
    {
        return $this->hasMany(CourseContent::class, 'course_id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'course_id');
    }

    public function instructors()
    {
        return $this->belongsToMany(Instructor::class, 'course_instructors', 'course_id', 'instructor_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'course_categories', 'course_id', 'category_id');
    }
}