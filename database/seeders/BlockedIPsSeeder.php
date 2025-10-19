<?php
// database/seeders/BlockedIPsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BlockedIPsSeeder extends Seeder
{
    public function run(): void
    {
        $blockedIPs = [];
        $blockId = 5000;

        $reasons = [
            'Intento de acceso no autorizado',
            'Actividad sospechosa repetitiva',
            'Ataque de fuerza bruta detectado',
            'Comportamiento malicioso identificado'
        ];

        for ($i = 0; $i < 10; $i++) {
            $blockedIPs[] = [
                'id_blocked_ip' => $blockId + $i,
                'ip_address' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                'reason' => $reasons[rand(0, 3)],
                'block_date' => Carbon::now()->subDays(rand(1, 60)),
                'active' => (bool)rand(0, 1),
            ];
        }

        DB::table('blocked_ips')->insert($blockedIPs);
    }
}