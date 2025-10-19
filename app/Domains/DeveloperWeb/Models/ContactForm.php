<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactForm extends Model
{
    use HasFactory;

    protected $table = 'contact_forms';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos de Laravel
    public $timestamps = false;

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

    // Mapear las columnas personalizadas si quieres usar métodos como save()
    const CREATED_AT = 'submission_date';
    const UPDATED_AT = null; // No hay columna updated_at

    public function assignedTo()
    {
        return $this->belongsTo(\App\Domains\Administrator\Models\Employee::class, 'assigned_to');
    }
}