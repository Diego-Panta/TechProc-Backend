<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diploma extends Model
{
    use HasFactory;

    protected $table = 'diplomas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'program_id',
        'issue_date',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}