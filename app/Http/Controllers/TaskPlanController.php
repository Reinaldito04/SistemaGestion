<?php

namespace App\Http\Controllers;

use App\Models\TaskPlan;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;

class TaskPlanController extends Controller
{

use ControllerTrait;

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
     
    ]);

    // 🔎 Columnas buscables y filtrables por periodo
    $searchableColumns = ['id', 'title', 'description'];
    $searchablePeriodColumns = ['start_at', 'end_at','created_at', ];

    $query = TaskPlan::query(); 

   
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
    
}

