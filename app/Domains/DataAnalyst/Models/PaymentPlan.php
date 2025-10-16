<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $table = 'payment_plans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'invoice_id',
        'installments',
        'installment_amount',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'installments' => 'integer',
        'installment_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}