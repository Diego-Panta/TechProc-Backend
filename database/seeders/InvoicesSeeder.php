<?php
// database/seeders/InvoicesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoicesSeeder extends Seeder
{
    public function run(): void
    {
        $enrollments = DB::table('enrollments')->get();
        $revenueSources = DB::table('revenue_sources')->get();

        $invoices = [];
        $invoiceNumber = 10000;

        foreach ($enrollments as $index => $enrollment) {
            $totalAmount = rand(500, 2000) + (rand(0, 99) / 100);
            
            $invoices[] = [
                'enrollment_id' => $enrollment->id,
                'revenue_source_id' => $revenueSources->random()->id,
                'invoice_number' => 'INV-' . ($invoiceNumber + $index),
                'issue_date' => Carbon::now()->subDays(rand(1, 60)),
                'total_amount' => $totalAmount,
                'status' => ['Pending', 'Paid', 'Cancelled'][rand(0, 2)],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('invoices')->insert($invoices);
    }
}