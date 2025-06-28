<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use Laratrust\Models\Permission;


class DepartmentController extends Controller
{
    
    use ControllerTrait;


       public function __construct() {
      
        $this->middleware('permission:departments-browse', ['only' => ['index']]);
        $this->middleware('permission:departments-read', ['only' => ['show']]);
        $this->middleware('permission:departments-edit', ['only' => ['update']]);
        $this->middleware('permission:departments-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
      $aditionalValidation = $request->validate([
            'filter_active' => 'boolean',
          
        ]);
        $searchableColumns = ['id', 'name', 'display_name', 'description'];
        $query = Department::query();

        if (isset($aditionalValidation['filter_active'])) {
            $query->where('active', $aditionalValidation['filter_active']);
        }

        $query = $this->find($request, $query, $searchableColumns);
        $results = $this->paginate($request, $query, $searchableColumns);
        return response()->json($results);
    }

    public function show($id)
    {
        $query = Department::query();
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
            'name' => 'required|string|max:255|unique:departments,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $department = Department::create($request->only(['name', 'display_name', 'description', 'active']));
        return response()->json(['data' => $department], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);
        $department = Department::find($id);
    if (!$department) {
        return response()->json(['message' => 'Departamento no encontrado'], 404);
    }           
    $department->update($request->only(['name', 'display_name', 'description', 'active'])); 
    return response()->json(['data' => $department,
        'message' => 'Departamento actualizado exitosamente.'], 200);
    }

public function destroy(string $uuid)
{
     $query = Department::query();

    $response = $this->eraseById($query, $uuid);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Departamento eliminado correctamente']);
}
}
