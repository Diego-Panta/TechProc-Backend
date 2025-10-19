<?php
// database/seeders/SecurityAlertsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecurityAlertsSeeder extends Seeder
{
    public function run(): void
    {
        $blockedIPs = DB::table('blocked_ips')->get();

        $securityAlerts = [];
        $alertId = 6000;

        $threatTypes = [
            'Brute Force Attack',
            'SQL Injection Attempt',
            'XSS Attack',
            'DDoS Attempt',
            'Unauthorized Access'
        ];

        $severities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['new', 'investigating', 'resolved', 'false_positive'];

        foreach ($blockedIPs as $index => $blockedIP) {
            $securityAlerts[] = [
                'id_security_alert' => $alertId + $index,
                'threat_type' => $threatTypes[rand(0, 4)],
                'severity' => $severities[rand(0, 3)],
                'status' => $statuses[rand(0, 3)],
                'blocked_ip_id' => $blockedIP->id,
                'detection_date' => $blockedIP->block_date,
            ];
        }

        DB::table('security_alerts')->insert($securityAlerts);
    }
}