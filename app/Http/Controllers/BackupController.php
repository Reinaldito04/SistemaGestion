<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackupController extends Controller
{

public function __construct()
    {
        $this->middleware('permission:backups-read', ['only' => ['downloadSQL']]);
    }

    public function downloadSQL()
    {
        $dbConnection = env('DB_CONNECTION');
        $dbName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $filename = storage_path("app/backup_{$dbName}_" . date('Ymd_His') . ".sql");

        if ($dbConnection === 'mysql') {
            // Opciones para un dump portable y sin información de usuario ni create database
            $command = "mysqldump --user=\"{$username}\" --password=\"{$password}\" --host=\"{$host}\" --no-create-db --skip-comments --skip-set-charset --single-transaction --quick --lock-tables=false {$dbName} > \"{$filename}\"";
        } elseif ($dbConnection === 'sqlite' || $dbConnection === 'sqlite3') {
            $dbPath = database_path(env('DB_DATABASE'));
            $command = "sqlite3 \"{$dbPath}\" .dump > \"{$filename}\"";
        } else {
            return response()->json(['error' => 'Tipo de base de datos no soportado'], 400);
        }

        exec($command, $output, $resultCode);

        if ($resultCode === 0 && file_exists($filename)) {
            return response()->download($filename)->deleteFileAfterSend(true);
        } else {
            return response()->json(['error' => 'Error al generar el backup'], 500);
        }
    }
}