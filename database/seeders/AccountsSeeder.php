<?php
// database/seeders/AccountsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('accounts')->insert([
            [
                'code' => '1001',
                'description' => 'Cuenta Corriente Principal',
                'account_type' => 'Asset',
                'current_balance' => 50000.00,
            ],
            [
                'code' => '4001',
                'description' => 'Ingresos por Matrículas',
                'account_type' => 'Income',
                'current_balance' => 0.00,
            ],
            [
                'code' => '4002',
                'description' => 'Ingresos por Cursos',
                'account_type' => 'Income',
                'current_balance' => 0.00,
            ],
            [
                'code' => '5001',
                'description' => 'Gastos Operativos',
                'account_type' => 'Expense',
                'current_balance' => 0.00,
            ],
            [
                'code' => '2001',
                'description' => 'Obligaciones Financieras',
                'account_type' => 'Liability',
                'current_balance' => 0.00,
            ],
        ]);
    }
}