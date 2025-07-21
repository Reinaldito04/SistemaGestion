<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use Laratrust\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
     use ControllerTrait;

          public function __construct() {

        $this->middleware('permission:tasks-browse', ['only' => ['index']]);
        $this->middleware('permission:tasks-read', ['only' => ['show']]);
        $this->middleware('permission:tasks-edit', ['only' => ['update']]);
        $this->middleware('permission:tasks-delete', ['only' => ['destroy']]);
    }


   public function index(Request $request)
{
    // ⚙️ Validación de filtros
    $request->validate([
        'filter_created_by' => 'nullable|integer|exists:users,id',
        'filter_audited_by' => 'nullable|integer|exists:users,id',
        'filter_participant_ids' => 'nullable|array',
        'filter_participant_ids.*' => 'integer|exists:users,id',
         'filter_status' => 'nullable|string|in:En proceso,Ejecutado,Aprobado,Cancelado',
        'filter_sector_id' => 'nullable|integer|exists:sectors,id',
        'filter_plant_id' => 'nullable|integer|exists:plants,id',
        'filter_area_id' => 'nullable|integer|exists:areas,id',
    ]);

    // 🔎 Columnas buscables y filtrables por periodo
    $searchableColumns = ['id', 'title', 'description'];
    $searchablePeriodColumns = ['created_at', 'executed_at', 'approved_at', 'canceled_at', 'deadline_at'];

    $query = Task::query()->with('files'); 

    // 🎯 Filtros directos
    if ($filterCreatedBy = $request->input('filter_created_by')) {
        $query->where('created_by', $filterCreatedBy);
    }

    if ($filterAuditedBy = $request->input('filter_audited_by')) {
        $query->where('audited_by', $filterAuditedBy);
    }

    if ($participantIds = $request->input('filter_participant_ids')) {
        $query->whereHas('participants', function ($q) use ($participantIds) {
            $q->whereIn('users.id', $participantIds);
        });
    }

    if ($filterSectorId = $request->input('filter_sector_id')) {
        $query->where('sector_id', $filterSectorId);
    }

    if ($filterPlantId = $request->input('filter_plant_id')) {
        $query->whereHas('sector.plant', function ($q) use ($filterPlantId) {
            $q->where('id', $filterPlantId);
        });
    }

    if ($filterAreaId = $request->input('filter_area_id')) {
        $query->whereHas('sector.plant.area', function ($q) use ($filterAreaId) {
            $q->where('id', $filterAreaId);
        });
    }

    // 🔁 Filtro por estado usando scopes
   if ($filterStatus = $request->input('filter_status')) {
    match ($filterStatus) {
        'En proceso'    => $query->enProceso(),
        'Ejecutado'     => $query->ejecutado(),
        'Aprobado'      => $query->aprobado(),
        'Cancelado'     => $query->cancelado(),
    };
}
    // 🔍 Búsqueda libre y filtros de fecha (period_filters[])
    $query = $this->find($request, $query, $searchableColumns, $searchablePeriodColumns);

    // 📦 Paginación y ordenamiento
    $results = $this->paginate($request, $query, $searchableColumns);

    return response()->json($results);
}

    


        public function show($id)
    {
        $query = Task::query()->with('files'); 
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
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:2500'],
        'article_id' => ['required', 'exists:articles,id'],
        'sector_id' => ['required', 'exists:sectors,id'],
        'deadline_at' => ['nullable', 'date'],
    ]);

    $user = auth()->user();

    $model = Task::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'created_by' => $user->id,
        'article_id' => $request->input('article_id'),
        'sector_id' => $request->input('sector_id'),
        'deadline_at' => $request->input('deadline_at'),
    ]);

    // 🔗 Aquí agregamos al usuario como participante
   $model->participants()->syncWithoutDetaching([$user->id]);


    return response()->json(['data' => $model], 201);
}


public function asignarParticipantes(Request $request)
{
    $request->validate([
        'task_id' => ['required', 'exists:tasks,id'],
        'user_ids' => ['required', 'array', 'min:1'],
        'user_ids.*' => ['exists:users,id'],
    ]);

    $task = Task::findOrFail($request->task_id);

    if ($task->created_by !== Auth::id()) {
        return response()->json(['error' => 'Solo el creador de la actividad puede agregar participantes.'], 403);
    }

    $task->participants()->syncWithoutDetaching($request->user_ids);

    return response()->json(['message' => 'Participantes asignados correctamente'], 200);
}

public function revocarParticipantes(Request $request)
{
    $request->validate([
        'task_id' => ['required', 'exists:tasks,id'],
        'user_ids' => ['required', 'array', 'min:1'],
        'user_ids.*' => ['exists:users,id'],
    ]);

    $task = Task::findOrFail($request->task_id);

    if ($task->created_by !== Auth::id()) {
        return response()->json(['error' => 'Solo el creador de la actividad puede revocar participantes.'], 403);
    }

    $currentIds = $task->participants()->pluck('users.id')->toArray();
    $revocarIds = array_intersect($currentIds, $request->user_ids);
    $remaining = array_diff($currentIds, $revocarIds);

    if (count($remaining) < 1) {
        return response()->json(['error' => 'Debe quedar al menos un participante asignado a la actividad'], 422);
    }

    $task->participants()->detach($revocarIds);

    return response()->json(['message' => 'Participantes revocados correctamente'], 200);
}

public function update(Request $request,$id)
{
    $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:2500'],
        'article_id' => ['required', 'exists:articles,id'],
        'sector_id' => ['required', 'exists:sectors,id'],
        'deadline_at' => ['nullable', 'date'],
    ]);

    $task = Task::find($id);


          
    if (!$task) {
        return response()->json(['message' => 'Actividad no encontrada'], 404);
    }      


    $user = Auth::user();

    if ($user->id !== $task->created_by && ! $user->hasPermission('tasks-supervise')) {
        return response()->json(['error' => 'No tienes permiso para editar esta actividad.'], 403);
    }

    $task->update([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'article_id' => $request->input('article_id'),
        'sector_id' => $request->input('sector_id'),
        'deadline_at' => $request->input('deadline_at'),
    ]);

    return response()->json(['message' => 'Actividad actualizada correctamente', 'data' => $task], 200);
}

public function executeActivity(Request $request)
{
    $request->validate([
        'task_id' => ['required', 'exists:tasks,id'],
    ]);

    $task = Task::findOrFail($request->task_id);
    $user = Auth::user();

    // 🔒 Solo el creador puede ejecutarla
    if ($user->id !== $task->created_by) {
        return response()->json([
            'error' => 'No tienes permiso para ejecutar esta actividad.',
        ], 403);
    }

    $status = $task->status;

    // ⛔ Solo se puede ejecutar si está "En proceso"
    if ($status !== 'En proceso') {
        return response()->json([
            'error' => 'No se puede ejecutar la actividad porque ya se encuentra en el estatus ' . $status,
        ], 422);
    }

    // ✅ Ejecutar la actividad
    $task->executed_at = now();
    $task->save();

    return response()->json([
        'message' => 'Actividad ejecutada exitosamente.',
    ], 200);
}
public function cancelarActividad(Request $request)
{
    $request->validate([
        'task_id' => ['required', 'exists:tasks,id'],
    ]);

    $task = Task::findOrFail($request->task_id);
    $user = Auth::user();

  if (! $user->hasPermission('tasks-supervise')) {
        return response()->json([
            'error' => 'No tienes permiso para cancelar esta actividad.',
        ], 403);
    }

    $status = $task->status;

    // 🚫 Actividades que no pueden ser canceladas
    if (in_array($status, ['Aprobado', 'Cancelado', 'Indeterminado'])) {
        return response()->json([
            'error' => 'No se puede cancelar la actividad porque ya se encuentra en el estatus: ' . $status,
        ], 422);
    }

    // ✅ Puede ser cancelada
    if (in_array($status, ['En proceso', 'Ejecutado'])) {
        $task->declineBy($user);
        $task->save();
       
        return response()->json([
            'message' => 'Actividad cancelada exitosamente.',
        ], 200);
    }

    // 🔁 Fallback por si aparece un estado inesperado
    return response()->json([
        'error' => 'No se puede cancelar la actividad porque ya se encuentra en el estatus: ' . $status,
    ], 422);
}

public function approveActivity(Request $request)
{
    $request->validate([
        'task_id' => ['required', 'exists:tasks,id'],
    ]);

    $task = Task::findOrFail($request->task_id);
    $user = Auth::user();

    // 🔒 Solo usuarios con permiso de supervisión pueden aprobar
    if (! $user->hasPermission('tasks-supervise')) {
        return response()->json([
            'error' => 'No tienes permiso para aprobar esta actividad.',
        ], 403);
    }

    $status = $task->status;

    // ⛔ Validación: solo se aprueba si está en estado Ejecutado
    if ($status !== 'Ejecutado') {
        return response()->json([
             'error' => 'No se puede aprobar la actividad porque ya se encuentra en el estatus: ' . $status,
        ], 422);
    }

    // ✅ Aprobar actividad
    $task->approveBy($user);
    $task->save();

    return response()->json([
        'message' => 'Actividad aprobada exitosamente.',
    ], 200);
}


public function addComments(Request $request,$id)
{
    $request->validate([
        'title' => 'nullable|string|max:255',
        'body' => 'required|string|max:1000',
    ]);


    $query = Task::query();
    
    $query->where('id', $id);

    $query->select( 'id');

    $data = $query->first();

  
    if (!isset($data)) {
        return response()->json(['error' => 'Registro no encontrado'], 404);
    }

    $commentData = [
        'title' =>  $request->title ?? "Comentario",
        'body' => $request->body,
        'active' => true
    ];

    $comment = $data->comment($commentData, Auth::user());

    return response()->json(
        [
         'message' => 'Comentario agregado exitosamente',
         'data'=>$comment
        ], 201);
}


public function showComments($id)
{
    $task = Task::find($id);

    if (!$task) {
        return response()->json(['error' => 'Registro no encontrado'], 404);
    }
   $comments= $task->comments()->with('creator')->get();
    return response()->json(['data' =>$comments], 200);
}

public function uploadFiles(Request $request)
{
    $request->validate([
        'task_id' => 'required|exists:tasks,id',
        'files' => 'required|array|min:1',
        'files.*' => 'required|file|mimes:pdf|max:10240',
    ]);

    DB::beginTransaction();

    try {
        $model = Task::findOrFail($request->task_id);
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

            $model->files()->attach($file->id);
            $savedFiles[] = $file;
        }

        DB::commit();

        return response()->json([
            'message' => 'Todos los archivos fueron cargados y vinculados exitosamente.',
            'files' => $savedFiles,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al subir archivos amodel: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error al procesar los archivos. No se guardó nada.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function deleteFiles(Request $request)
{
    $request->validate([
        'task_id' => 'required|exists:tasks,id',
        'file_ids' => 'required|array|min:1',
        'file_ids.*' => 'required|integer|distinct|exists:files,id',
    ]);

    DB::beginTransaction();

    try {
        $model = Task::findOrFail($request->task_id);

        // Forzar índices numéricos
        $fileIds = array_values($request->input('file_ids'));

        // Obtener IDs de archivos vinculados
        $linkedFileIds = $model->files()->pluck('files.id')->toArray();

        // Validar que todos estén vinculados
        $notLinked = array_diff($fileIds, $linkedFileIds);

        if (count($notLinked) > 0) {
            DB::rollBack();

            return response()->json([
                'message' => 'Algunos archivos no están vinculados a la actividad. No se eliminó nada.',
                'not_linked' => array_values($notLinked),           
 ], 422);
        }

        // Eliminar asociaciones y registros
        foreach ($fileIds as $fileId) {
            $model->files()->detach($fileId);
            File::find($fileId)?->delete();
        }

        DB::commit();

        return response()->json([
            'message' => 'Todos los archivos fueron eliminados correctamente.',
            'deleted' => $fileIds,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al eliminar archivos de la actividad: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error inesperado. No se eliminó nada.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function destroy($id)
{
    $task = Task::find($id);        
    if (!$task) {
        return response()->json(['message' => 'Actividad no encontrada'], 404);
    }

    if ($task->created_by !== auth()->id()) {
        return response()->json(['message' => 'Solo el creador de la actividad puede eliminar la actividad'], 403);
    }

    DB::beginTransaction();

    try {
        // Eliminar comentarios relacionados
        $task->comments()->delete();

        // Eliminar archivos relacionados en la tabla pivote
        $task->files()->detach();

        // Eliminar la tarea
        $task->delete();

        DB::commit();

        return response()->json(['message' => 'Actividad eliminada correctamente'], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error al eliminar la actividad',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
