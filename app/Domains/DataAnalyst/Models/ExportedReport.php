<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportedReport extends Model
{
    protected $table = 'exported_reports';

    protected $fillable = [
        'report_type',
        'format',
        'file_name',
        'file_path',
        'report_title',
        'file_size',
        'filters',
        'description',
        'record_count',
        'generated_by',
        'access_token',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'expires_at' => 'datetime',
        'file_size' => 'integer',
        'record_count' => 'integer',
    ];

    /**
     * Relación con el usuario que generó el reporte
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Scope para reportes no expirados
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('expires_at', '>', now())
              ->orWhereNull('expires_at');
        });
    }

    /**
     * Scope para reportes recientes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope por tipo de reporte
     */
    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope por formato
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope por usuario
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('generated_by', $userId);
    }

    /**
     * Verificar si el reporte ha expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verificar si el archivo existe en storage
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Obtener URL de descarga
     */
    public function getDownloadUrl(): string
    {
        return route('api.data-analyst.export.download', $this->access_token);
    }

    /**
     * Obtener tamaño formateado del archivo
     */
    protected function formattedFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->file_size >= 1073741824) {
                    return number_format($this->file_size / 1073741824, 2) . ' GB';
                } elseif ($this->file_size >= 1048576) {
                    return number_format($this->file_size / 1048576, 2) . ' MB';
                } elseif ($this->file_size >= 1024) {
                    return number_format($this->file_size / 1024, 2) . ' KB';
                } else {
                    return $this->file_size . ' bytes';
                }
            }
        );
    }

    /**
     * Obtener icono según tipo de reporte
     */
    protected function reportIcon(): Attribute
    {
        return Attribute::make(
            get: function () {
                $icons = [
                    'students' => 'users',
                    'courses' => 'book',
                    'attendance' => 'calendar',
                    'grades' => 'chart-bar',
                    'financial' => 'currency-dollar',
                    'tickets' => 'ticket',
                    'security' => 'shield-check',
                    'dashboard' => 'chart-pie',
                ];

                return $icons[$this->report_type] ?? 'document';
            }
        );
    }

    /**
     * Obtener nombre amigable del tipo de reporte
     */
    protected function reportTypeName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $names = [
                    'students' => 'Estudiantes',
                    'courses' => 'Cursos',
                    'attendance' => 'Asistencia',
                    'grades' => 'Calificaciones',
                    'financial' => 'Financiero',
                    'tickets' => 'Tickets de Soporte',
                    'security' => 'Seguridad',
                    'dashboard' => 'Dashboard General',
                ];

                return $names[$this->report_type] ?? 'Reporte';
            }
        );
    }

    /**
     * Generar token de acceso único
     */
    public static function generateAccessToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('access_token', $token)->exists());

        return $token;
    }

    /**
     * Limpiar reportes expirados
     */
    public static function cleanupExpiredReports(): int
    {
        $expired = self::where('expires_at', '<=', now())->get();
        $deletedCount = 0;

        foreach ($expired as $report) {
            // Eliminar archivo físico
            if ($report->fileExists()) {
                Storage::delete($report->file_path);
            }
            // Eliminar registro de la base de datos
            $report->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }
}