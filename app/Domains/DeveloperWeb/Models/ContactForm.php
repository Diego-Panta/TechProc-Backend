<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactForm extends Model
{
    use HasFactory;

    protected $table = 'contact_forms';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_contact',
        'full_name',
        'email',
        'phone',
        'company',
        'subject',
        'message',
        'form_type',
        'status',
        'assigned_to',
        'response',
        'response_date',
        'submission_date',
    ];

    protected $casts = [
        'response_date' => 'datetime',
        'submission_date' => 'datetime',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
}