<?php

namespace App\Http\Controllers;

use App\Models\Ier;
use App\Models\File;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IerController extends Controller
{
  
    use ControllerTrait;


       public function __construct() {
      
        $this->middleware('permission:iers-browse', ['only' => ['index']]);
        $this->middleware('permission:iers-read', ['only' => ['show']]);
        $this->middleware('permission:iers-edit', ['only' => ['update']]);
        $this->middleware('permission:iers-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
      
      $aditionalValidation = $request->validate([
            'filter_active' => 'boolean',
          
        ]);
        $searchableColumns = ['id', 'name', 'display_name', 'description'];
        $query = Ier::query()
            ->with('files'); 

        if (isset($aditionalValidation['filter_active'])) {
            $query->where('active', $aditionalValidation['filter_active']);
        }

        $query = $this->find($request, $query, $searchableColumns);
        
        $results = $this->paginate($request, $query, $searchableColumns);

        return response()->json($results);
    }

    public function show($id)
    {
        $query = Ier::query();
        try {
            $data = $this->retrieveById($query, $id);
            return response()->json(['data' => $data->toArray()]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:iers,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
            'plant_id' => 'required|integer|exists:plants,id',
        ]);


        $model = Ier::create($request->only(['name', 'display_name', 'description', 'active']));

        if ($request->filled('plant_id')) {
            $model->plant()->attach($request->plant_id);
        }
        return response()->json(['data' => $model], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:iers,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
            'plant_id' => 'nullable|integer|exists:plants,id',

        ]);
        $model = Ier::find($id);
    if (!$model) {
        return response()->json(['message' => 'Ier no encontrado'], 404);
    }           
    $model->update($request->only(['name', 'display_name', 'description', 'active'])); 

      if ($request->filled('plant_id')) {
    $model->plant()->sync([$request->plant_id]);
}
    return response()->json(['data' => $model,
        'message' => 'Ier actualizado exitosamente.'], 200);
    }

public function destroy(string $id)
{
    $query = Ier::query();
    $ier = $query->where('id', $id)->first();

    if (! $ier) {
        return response()->json(['message' => 'Ier no encontrado.'], 404);
    }

    // Eliminar archivos relacionados
    $fileIds = $ier->files()->pluck('files.id')->toArray();

    if (!empty($fileIds)) {
        $ier->files()->detach($fileIds);
        File::whereIn('id', $fileIds)->delete();
    }

    // Eliminar el Ier
    $response = $this->eraseById($query, $id);

    if ($response->getStatusCode() !== 200) {
        return $response;
    }

    return response()->json(['message' => 'Ier eliminado correctamente']);
}




public function uploadFilesToIer(Request $request)
{
    $request->validate([
        'ier_id' => 'required|exists:iers,id',
        'files' => 'required|array|min:1',
        'files.*' => 'required|file|mimes:pdf|max:10240',
    ]);

    DB::beginTransaction();

    try {
        $ier = Ier::findOrFail($request->ier_id);
        $savedFiles = [];

        foreach ($request->file('files') as $uploadedFile) {
            $base64 = base64_encode(file_get_contents($uploadedFile->getRealPath()));

            $file = File::create([
                'name' => $uploadedFile->getClientOriginalName(),
                'file_extension' => $uploadedFile->getClientOriginalExtension(),
                'file_size' => $uploadedFile->getSize(),
                'compressed_file_size' => null,
                'file_base64' => $base64,
            ]);

            $ier->files()->attach($file->id);
            $savedFiles[] = $file;
        }

        DB::commit();

        return response()->json([
            'message' => 'Todos los archivos fueron cargados y vinculados exitosamente.',
            'files' => $savedFiles,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al subir archivos al Ier: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error al procesar los archivos. No se guardó nada.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function deleteFilesFromIer(Request $request)
{
    $request->validate([
        'ier_id' => 'required|exists:iers,id',
        'file_ids' => 'required|array|min:1',
        'file_ids.*' => 'required|integer|distinct|exists:files,id',
    ]);

    DB::beginTransaction();

    try {
        $ier = Ier::findOrFail($request->ier_id);

        // Forzar índices numéricos
        $fileIds = array_values($request->input('file_ids'));

        // Obtener IDs de archivos vinculados
        $linkedFileIds = $ier->files()->pluck('files.id')->toArray();

        // Validar que todos estén vinculados
        $notLinked = array_diff($fileIds, $linkedFileIds);

        if (count($notLinked) > 0) {
            DB::rollBack();

            return response()->json([
                'message' => 'Algunos archivos no están vinculados al Ier. No se eliminó nada.',
                'not_linked' => array_values($notLinked),           
 ], 422);
        }

        // Eliminar asociaciones y registros
        foreach ($fileIds as $fileId) {
            $ier->files()->detach($fileId);
            File::find($fileId)?->delete();
        }

        DB::commit();

        return response()->json([
            'message' => 'Todos los archivos fueron eliminados correctamente.',
            'deleted' => $fileIds,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al eliminar archivos del Ier: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error inesperado. No se eliminó nada.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
