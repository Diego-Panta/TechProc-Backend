<?php
// database/seeders/IncidentsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncidentsSeeder extends Seeder
{
    public function run(): void
    {
        $securityAlerts = DB::table('security_alerts')->whereIn('severity', ['high', 'critical'])->get();
        $employees = DB::table('employees')->get();

        $incidents = [];
        $incidentId = 7000;

        foreach ($securityAlerts as $index => $alert) {
            $incidents[] = [
                'id_incident' => $incidentId + $index,
                'alert_id' => $alert->id,
                'responsible_id' => $employees->random()->id,
                'title' => 'Incidente de Seguridad - ' . $alert->threat_type,
                'status' => ['open', 'in_progress', 'resolved'][rand(0, 2)],
                'report_date' => $alert->detection_date,
            ];
        }

        DB::table('incidents')->insert($incidents);
    }
}