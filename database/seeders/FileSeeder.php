<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        File::truncate();
        Schema::enableForeignKeyConstraints();

        $folderPath = storage_path('app/seed_files'); // Asegúrate de que esta carpeta exista y tenga tus PDFs

        $files = glob($folderPath . '/*.pdf');

        $this->command->getOutput()->progressStart(count($files));

        foreach ($files as $index => $filePath) {
            $fileContents = file_get_contents($filePath);
            $fileSize = filesize($filePath);
            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileBase64 = base64_encode($fileContents);

            $file = new File();
            //$file->id = $index + 1;
            $file->name = $fileName;
            $file->file_extension = $fileExtension;
            $file->file_size = $fileSize;
            $file->file_base64 = $fileBase64;
            $file->save();

            usleep(50000);
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
    }
}