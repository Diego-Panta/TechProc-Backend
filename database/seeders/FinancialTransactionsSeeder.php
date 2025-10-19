<?php
// database/seeders/FinancialTransactionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = DB::table('accounts')->get();
        $invoices = DB::table('invoices')->where('status', 'Paid')->get();
        $payments = DB::table('payments')->get();

        $transactions = [];

        // Transacciones de ingresos (payments)
        foreach ($payments as $payment) {
            $invoice = $invoices->where('id', $payment->invoice_id)->first();
            
            $transactions[] = [
                'account_id' => $accounts->where('account_type', 'Income')->random()->id,
                'amount' => $payment->amount,
                'transaction_date' => $payment->payment_date,
                'description' => 'Pago de matrícula - ' . $invoice->invoice_number,
                'transaction_type' => 'income',
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // Transacciones de gastos (ejemplo)
        $transactions[] = [
            'account_id' => $accounts->where('account_type', 'Expense')->random()->id,
            'amount' => 500.00,
            'transaction_date' => Carbon::now()->subDays(15),
            'description' => 'Pago de servicios de nube',
            'transaction_type' => 'expense',
            'invoice_id' => null,
            'payment_id' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        DB::table('financial_transactions')->insert($transactions);
    }
}