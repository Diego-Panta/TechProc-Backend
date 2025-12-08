<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

class CreateGoogleDriveFolder extends Command
{
    protected $signature = 'drive:create-folder {name=TechProc Images}';
    protected $description = 'Create a folder in Google Drive using Service Account and get its ID';

    public function handle()
    {
        try {
            $folderName = $this->argument('name');

            $this->info("Creating folder '{$folderName}' in Google Drive...");

            // Initialize Google Client
            $client = new Client();
            $client->setApplicationName(config('services.google_drive.app_name', 'TechProc Backend'));

            $credentialsPath = config('services.google_drive.credentials_path');

            if (!file_exists($credentialsPath)) {
                $this->error("Credentials file not found at: {$credentialsPath}");
                return 1;
            }

            $client->setAuthConfig($credentialsPath);
            $client->addScope(Drive::DRIVE);

            $driveService = new Drive($client);

            // Create folder metadata
            $folderMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            // Create the folder
            $folder = $driveService->files->create($folderMetadata, [
                'fields' => 'id, name, webViewLink'
            ]);

            $this->info('✅ Folder created successfully!');
            $this->newLine();

            $this->line("📁 Folder Name: {$folder->name}");
            $this->line("🆔 Folder ID: {$folder->id}");
            $this->line("🔗 Web Link: {$folder->webViewLink}");
            $this->newLine();

            // Make folder publicly readable (optional)
            if ($this->confirm('Do you want to make this folder publicly readable?', true)) {
                $permission = new Permission([
                    'type' => 'anyone',
                    'role' => 'reader'
                ]);

                $driveService->permissions->create($folder->id, $permission);
                $this->info('✅ Folder is now publicly readable');
            }

            $this->newLine();
            $this->info('📝 Next steps:');
            $this->line('1. Copy the Folder ID above');
            $this->line('2. Update your .env file:');
            $this->line("   GOOGLE_DRIVE_FOLDER_ID=\"{$folder->id}\"");
            $this->line('3. Run: php artisan config:clear');
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->error('Error creating folder: ' . $e->getMessage());
            return 1;
        }
    }
}
