<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Lms\Models\Enrollment;
class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $primaryKey = 'id';

    protected $fillable = [
        'enrollment_id',
        'revenue_source_id',
        'invoice_number',
        'issue_date',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function revenueSource()
    {
        return $this->belongsTo(RevenueSource::class, 'revenue_source_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class, 'invoice_id');
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'invoice_id');
    }
}