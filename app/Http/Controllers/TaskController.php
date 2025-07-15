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
      $aditionalValidation = $request->validate([
            
          
        ]);
        $searchableColumns = ['id','tittle', 'description'];
        $query = Task::query();

     

        $query = $this->find($request, $query, $searchableColumns);
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




}
