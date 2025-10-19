<?php

namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\Employee;

class Incident extends Model
{
    use HasFactory;

    protected $table = 'incidents';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_incident',
        'alert_id',
        'responsible_id',
        'title',
        'status',
        'report_date',
    ];

    protected $casts = [
        'report_date' => 'datetime',
    ];

    public function alert()
    {
        return $this->belongsTo(SecurityAlert::class, 'alert_id');
    }

    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }
}