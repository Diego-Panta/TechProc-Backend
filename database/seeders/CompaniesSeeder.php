<?php
// database/seeders/CompaniesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompaniesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('companies')->insert([
            [
                'name' => 'Tech Solutions SAC',
                'industry' => 'Tecnología',
                'contact_name' => 'Roberto Silva',
                'contact_email' => 'r.silva@techsolutions.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Innovate Perú EIRL',
                'industry' => 'Consultoría',
                'contact_name' => 'Laura Mendoza',
                'contact_email' => 'l.mendoza@innovateperu.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}