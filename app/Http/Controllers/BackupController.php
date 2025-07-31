<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

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
    $timestamp = date('Ymd_His');
    
    // Ruta real del archivo SQLite
 
    $sourcePath = base_path("database/database.sqlite");
    // Ruta destino temporal con nombre ajustado
    $destinationPath = storage_path("app/backup_{$dbName}_{$timestamp}.sqlite");

    if ($dbConnection === 'sqlite' || $dbConnection === 'sqlite3') {

        if (!file_exists($sourcePath)) {
            \Log::error("Archivo SQLite original no encontrado: {$sourcePath}");
            return response()->json([
                'error' => 'No se encontró el archivo de base de datos SQLite',
                'path' => $sourcePath
            ], 404);
        }

        if (!is_readable($sourcePath)) {
            \Log::error("No se puede leer el archivo SQLite: {$sourcePath}");
            return response()->json([
                'error' => 'Permiso denegado para leer el archivo SQLite',
                'path' => $sourcePath
            ], 403);
        }

        // Copia el archivo con nombre timestamp
        if (!copy($sourcePath, $destinationPath)) {
            \Log::error("Fallo al copiar el archivo SQLite");
            return response()->json([
                'error' => 'No se pudo crear la copia del archivo SQLite'
            ], 500);
        }

        // Descargar la copia
        return response()->download($destinationPath)->deleteFileAfterSend(true);
    }

    // Conservamos la lógica para MySQL intacta
    if ($dbConnection === 'mysql') {
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $filename = storage_path("app/backup_{$dbName}_{$timestamp}.sql");
        $command = "mysqldump --user=\"{$username}\" --password=\"{$password}\" --host=\"{$host}\" --no-create-db --skip-comments --skip-set-charset --single-transaction --quick --lock-tables=false {$dbName} > \"{$filename}\"";

        exec($command . ' 2>&1', $output, $resultCode);
        $logOutput = implode("\n", $output);

        if ($resultCode === 0 && file_exists($filename)) {
            return response()->download($filename)->deleteFileAfterSend(true);
        } else {
            \Log::error("Error ejecutando backup MySQL (code {$resultCode}):\n{$logOutput}");
            return response()->json([
                'error' => 'Error al generar el backup',
                'details' => $logOutput,
                'code' => $resultCode
            ], 500);
        }
    }

    return response()->json([
        'error' => 'Tipo de base de datos no soportado',
        'db' => $dbConnection
    ],400);
}

}