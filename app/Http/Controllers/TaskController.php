<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use Laratrust\Models\Permission;
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
    $searchablePeriodColumns = ['created_at', 'executed_at', 'approved_at', 'canceled_at'];

    $query = Task::query();

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
        $query = Task::query();
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
    ]);

    $user = auth()->user();

    $model = Task::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'created_by' => $user->id,
        'article_id' => $request->input('article_id'),
        'sector_id' => $request->input('sector_id'),
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
    ]);

    return response()->json(['message' => 'Actividad actualizada correctamente', 'data' => $task], 200);
}

public function cerrarActividad(Request $request)
{
    $request->validate([
        'task_id' => ['required', 'exists:tasks,id'],
    ]);

    $task = Task::findOrFail($request->task_id);
    $user = Auth::user();

    // 🔒 Solo creador o con permiso
    if ($user->id !== $task->created_by && ! $user->hasPermission('tasks-supervise')) {
        return response()->json(['error' => 'No tienes permiso para cerrar esta actividad.'], 403);
    }

    // 📋 Validación del estado
    if ($task->status !== 'En proceso') {
        return response()->json([
            'error' => 'La actividad no puede cerrarse porque su estatus es: ' . $task->status,
        ], 422);
    }

    // 🛠️ Cierra y guarda la actividad
    $task->closeActivity();
    $task->save();

    return response()->json(['message' => 'Actividad cerrada exitosamente.'], 200);
}

public function cancelarActividad(Request $request)
{
    $request->validate([
        'task_id' => ['required', 'exists:tasks,id'],
    ]);

    $task = Task::findOrFail($request->task_id);
    $user = Auth::user();

    // 🔐 Validación de permiso
    if ($user->id !== $task->created_by && ! $user->hasPermission('tasks-supervise')) {
        return response()->json([
            'error' => 'No tienes permiso para cancelar esta actividad.',
        ], 403);
    }

    $status = $task->status;

    // 🚫 Actividades que no pueden ser canceladas
    if (in_array($status, ['Aprobado', 'Cancelado', 'Indeterminado'])) {
        return response()->json([
            'error' => 'No se puede cancelar la actividad porque ya se encuentra en el estatus "' . $status . '"',
        ], 422);
    }

    // ✅ Puede ser cancelada
    if (in_array($status, ['En proceso', 'Ejecutado'])) {
        $task->canceled_at = now();
        $task->save();

        return response()->json([
            'message' => 'Actividad cancelada exitosamente.',
        ], 200);
    }

    // 🔁 Fallback por si aparece un estado inesperado
    return response()->json([
        'error' => 'No se puede cancelar la actividad porque ya se encuentra en el estatus "' . $status . '"',
    ], 422);
}


}
