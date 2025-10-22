<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\DeveloperWeb\Enums\ContactFormStatus;

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

    /**
     * Accessor seguro para el estado
     */
    public function getStatusAttribute($value): ContactFormStatus
    {
        return ContactFormStatus::tryFrom($value);
    }

    /**
     * Mutator seguro para el estado
     */
    public function setStatusAttribute($value): void
    {
        if ($value instanceof ContactFormStatus) {
            $this->attributes['status'] = $value->value;
        } else {
            $this->attributes['status'] = ContactFormStatus::tryFrom($value)->value;
        }
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeByStatus($query, $status)
    {
        if ($status instanceof ContactFormStatus) {
            return $query->where('status', $status->value);
        } elseif (ContactFormStatus::isValid($status)) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope para formularios activos (no finalizados)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ContactFormStatus::getActiveStatuses());
    }

    /**
     * Scope para formularios finalizados
     */
    public function scopeFinalized($query)
    {
        return $query->whereIn('status', ContactFormStatus::getFinalStatuses());
    }

    /**
     * Scope para formularios pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', ContactFormStatus::PENDING->value);
    }

    /**
     * Verificar si el formulario está en estado final
     */
    public function isFinalized(): bool
    {
        $status = ContactFormStatus::safeFrom($this->getRawOriginal('status'));
        return $status ? $status->isFinal() : false;
    }

    /**
     * Verificar si el formulario puede ser editado
     */
    public function canBeEdited(): bool
    {
        return !$this->isFinalized();
    }

    /**
     * Obtener el label del estado actual
     */
    public function getStatusLabel(): string
    {
        $status = ContactFormStatus::safeFrom($this->getRawOriginal('status'));
        return $status ? $status->label() : 'Desconocido';
    }

    /**
     * Obtener el valor original del estado (para evitar problemas con el cast)
     */
    public function getRawStatus(): string
    {
        return $this->getRawOriginal('status') ?? ContactFormStatus::PENDING->value;
    }
}