<?php
// database/seeders/PaymentsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = DB::table('invoices')->where('status', 'Paid')->get();
        $paymentMethods = DB::table('payment_methods')->get();

        $payments = [];

        foreach ($invoices as $invoice) {
            $payments[] = [
                'invoice_id' => $invoice->id,
                'payment_method_id' => $paymentMethods->random()->id,
                'amount' => $invoice->total_amount,
                'payment_date' => Carbon::now()->subDays(rand(1, 30)),
                'status' => 'Completed',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('payments')->insert($payments);
    }
}