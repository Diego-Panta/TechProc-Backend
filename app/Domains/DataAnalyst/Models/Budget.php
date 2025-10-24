<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budgets';
    protected $primaryKey = 'id_budget';

    protected $fillable = [
        'category',
        'academic_period_id',
        'assigned_amount',
        'executed_amount',
        'creation_date',
        'modification_date',
        'approver_user_id',
    ];

    protected $casts = [
        'assigned_amount' => 'decimal:2',
        'executed_amount' => 'decimal:2',
        'creation_date' => 'datetime',
        'modification_date' => 'datetime',
    ];

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'budget_id');
    }
}