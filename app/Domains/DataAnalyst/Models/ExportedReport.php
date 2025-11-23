<?php
// app/Domains/DataAnalyst/Models/ExportedReport.php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExportedReport extends Model
{
    protected $table = 'exported_reports';
    
    protected $fillable = [
        'id',
        'report_type',
        'format',
        'file_name',
        'file_path',
        'file_size',
        'filters',
        'description',
        'record_count',
        'access_token',
        'expires_at'
    ];

    protected $casts = [
        'filters' => 'array',
        'file_size' => 'integer',
        'record_count' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            
            if (empty($model->access_token)) {
                $model->access_token = Str::random(32);
            }
            
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addDays(7); // Expira en 7 días
            }
        });
    }

    /**
     * Verificar si el reporte ha expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Obtener la URL de descarga
     */
    public function getDownloadUrl(): string
    {
        return route('api.data-analyst.reports.download', [
            'token' => $this->access_token
        ]);
    }

    /**
     * Obtener el tamaño del archivo formateado
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}