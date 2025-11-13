<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportLmsDataToCsv extends Command
{
    protected $signature = 'export:lms-data';
    protected $description = 'Exporta las tablas LMS a CSV para BigQuery';

    public function handle()
    {
        $tables = [
            'attendances', 'class_sessions', 'enrollments', 'groups',
            'grades', 'exams', 'enrollment_results', 'modules','course_versions',
            'courses', 'users', 'enrollment_payments', 'survey_responses', 'response_details',
            'tickets', 'appointments'
        ];

        foreach ($tables as $table) {
            $data = DB::table($table)->get();
            $filename = "exports/{$table}.csv";
            $path = storage_path("app/{$filename}");
            $handle = fopen($path, 'w');

            if ($data->count()) {
                fputcsv($handle, array_keys((array)$data[0]));
                foreach ($data as $row) {
                    fputcsv($handle, (array)$row);
                }
            }

            fclose($handle);
            $this->info("Exportado: {$filename}");
        }
    }
}
