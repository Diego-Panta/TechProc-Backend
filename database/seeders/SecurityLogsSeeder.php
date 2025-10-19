<?php
// database/seeders/SecurityLogsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecurityLogsSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();

        $securityLogs = [];
        $logId = 3000;

        $eventTypes = [
            'login_success',
            'login_failed', 
            'password_change',
            'profile_update',
            'access_denied',
            'session_timeout'
        ];

        foreach ($users as $index => $user) {
            for ($i = 0; $i < rand(1, 5); $i++) {
                $securityLogs[] = [
                    'id_security_log' => $logId + $index + $i,
                    'user_id' => $user->id,
                    'event_type' => $eventTypes[rand(0, 5)],
                    'description' => 'Evento de seguridad registrado para el usuario',
                    'source_ip' => '192.168.1.' . rand(1, 255),
                    'event_date' => Carbon::now()->subDays(rand(1, 90))->subHours(rand(1, 24)),
                ];
            }
        }

        DB::table('security_logs')->insert($securityLogs);
    }
}