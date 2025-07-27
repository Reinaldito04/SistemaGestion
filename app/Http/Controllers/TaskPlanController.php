<?php

namespace App\Http\Controllers;

use App\Models\TaskPlan;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;

class TaskPlanController extends Controller
{

use ControllerTrait;

 public function __construct() {

        $this->middleware('permission:task_plans-browse', ['only' => ['index']]);
        $this->middleware('permission:task_plans-read', ['only' => ['show']]);
        $this->middleware('permission:task_plans-edit', ['only' => ['update']]);
        $this->middleware('permission:task_plans-delete', ['only' => ['destroy']]);
    }

     public function index(Request $request)
{
    // ⚙️ Validación de filtros
    $request->validate([
       'filter_created_by' => 'nullable|integer|exists:users,id',
       'filter_audited_by' => 'nullable|integer|exists:users,id',
       'filter_participant_ids' => 'nullable|array',
       'filter_participant_ids.*' => 'integer|exists:users,id',
       'filter_sector_id' => 'nullable|integer|exists:sectors,id',
       'filter_plant_id' => 'nullable|integer|exists:plants,id',
       'filter_area_id' => 'nullable|integer|exists:areas,id',

    ]);

    // 🔎 Columnas buscables y filtrables por periodo
    $searchableColumns = ['id', 'title', 'description'];
    $searchablePeriodColumns = ['start_at', 'end_at','created_at', ];

    $query = TaskPlan::query(); 

    $query =  $query->with(['participants']) ;

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



    // 🔍 Búsqueda libre y filtros de fecha (period_filters[])
    $query = $this->find($request, $query, $searchableColumns, $searchablePeriodColumns);

    // 📦 Paginación y ordenamiento
    $results = $this->paginate($request, $query, $searchableColumns);

    return response()->json($results);
}


public function store(Request $request)
{
    $baseRules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'activity_title' => 'required|string|max:255',
        'activity_description' => 'nullable|string',
        'audited_by' => 'nullable|integer|exists:users,id',
        'article_id' => 'required|integer|exists:articles,id',
        'sector_id' => 'required|integer|exists:sectors,id',
        'frequency' => 'required|in:daily,weekly,monthly,yearly',
        'deadline_time' => 'required|date_format:H:i',
        'start_at' => 'required|date',
        'end_at' => 'nullable|date|after_or_equal:start_at',
        'is_active' => 'boolean'
    ];

    $frequency = $request->input('frequency');

    if ($frequency === 'weekly') {
        $baseRules['days'] = 'required|array|min:1';
        $baseRules['days.*'] = 'integer|between:0,6';
    } elseif ($frequency === 'monthly') {
        $baseRules['days'] = 'required|array|min:1';
       $baseRules['days.*'] = [
        'regex:/^(0?[1-9]|[12][0-9]|3[01]|last(-[1-9])?)$/'
    ];
    } else {
        $baseRules['days'] = 'prohibited';
    }

    $data = $request->validate($baseRules);

    $data['created_by'] = auth()->id();

    $model = TaskPlan::create($data);

     $user = auth()->user();

     $model->participants()->syncWithoutDetaching([$user->id]);

    return response()->json(['data' => $model], 201);
}


public function update(Request $request, $id)
{
    $model = TaskPlan::findOrFail($id);

    $baseRules = [
        'title' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|nullable|string',
        'activity_title' => 'sometimes|required|string|max:255',
        'activity_description' => 'sometimes|nullable|string',
        'audited_by' => 'sometimes|nullable|integer|exists:users,id',
        'article_id' => 'sometimes|required|integer|exists:articles,id',
        'sector_id' => 'sometimes|required|integer|exists:sectors,id',
        'frequency' => 'sometimes|required|in:daily,weekly,monthly,yearly',
        'deadline_time' => 'sometimes|required|date_format:H:i',
        'start_at' => 'sometimes|required|date',
        'end_at' => 'sometimes|nullable|date|after_or_equal:start_at',
        'is_active' => 'sometimes|boolean'
    ];

    // Determinar la frecuencia (del request, o del modelo si no viene en el request)
    $frequency = $request->input('frequency', $model->frequency);

    if ($frequency === 'weekly') {
        $baseRules['days'] = 'sometimes|required|array|min:1';
        $baseRules['days.*'] = 'integer|between:0,6';
    } elseif ($frequency === 'monthly') {
        $baseRules['days'] = 'sometimes|required|array|min:1';
        $baseRules['days.*'] = [
            'regex:/^(0?[1-9]|[12][0-9]|3[01]|last(-[1-9])?)$/'
        ];
    } else {
        $baseRules['days'] = 'prohibited';
    }

    $data = $request->validate($baseRules);

    $model->update($data);

    return response()->json(['data' => $model], 200);
}



        public function show($id)
    {
        $query = TaskPlan::query(); 

        $query->with(['participants']);
        try {
            $data = $this->retrieveById($query, $id);
            return response()->json(['data' => $data->toArray()]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }


    public function destroy($id)
{
     $query = TaskPlan::query();

    $response = $this->eraseById($query, $id);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Planificación eliminada correctamente']);
}


public function asignarParticipantes(Request $request)
{
    $request->validate([
        'task_plan_id' => ['required', 'exists:task_plans,id'],
        'user_ids' => ['required', 'array', 'min:1'],
        'user_ids.*' => ['exists:users,id'],
    ]);

    $taskPlan = TaskPlan::findOrFail($request->task_plan_id);


    $taskPlan->participants()->syncWithoutDetaching($request->user_ids);

    return response()->json(['message' => 'Participantes asignados correctamente'], 200);
}

public function revocarParticipantes(Request $request)
{
    $request->validate([
        'task_plan_id' => ['required', 'exists:task_plans,id'],
        'user_ids' => ['required', 'array', 'min:1'],
        'user_ids.*' => ['exists:users,id'],
    ]);

    $taskPlan = TaskPlan::findOrFail($request->task_plan_id);



    $currentIds = $taskPlan->participants()->pluck('users.id')->toArray();
    $revocarIds = array_intersect($currentIds, $request->user_ids);
    $remaining = array_diff($currentIds, $revocarIds);

    if (count($remaining) < 1) {
        return response()->json(['error' => 'Debe quedar al menos un participante asignado a la planificación'], 422);
    }

    $taskPlan->participants()->detach($revocarIds);

    return response()->json(['message' => 'Participantes revocados correctamente'], 200);
}
    
}

