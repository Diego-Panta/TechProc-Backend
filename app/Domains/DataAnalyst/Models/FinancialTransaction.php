<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $table = 'financial_transactions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'account_id',
        'amount',
        'transaction_date',
        'description',
        'transaction_type',
        'invoice_id',
        'payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}