<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use Laratrust\Models\Permission;


class PlantController extends Controller
{
    
    use ControllerTrait;


       public function __construct() {
      
        $this->middleware('permission:plants-browse', ['only' => ['index']]);
        $this->middleware('permission:plants-read', ['only' => ['show']]);
        $this->middleware('permission:plants-edit', ['only' => ['update']]);
        $this->middleware('permission:plants-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
      $aditionalValidation = $request->validate([
            'filter_active' => 'boolean',
            'filter_area_id' => 'integer|exists:areas,id',
          
        ]);
        $searchableColumns = ['id', 'name', 'display_name', 'description'];
        $query = Plant::query();

        if (isset($aditionalValidation['filter_active'])) {
            $query->where('active', $aditionalValidation['filter_active']);
        }

        if (isset($aditionalValidation['filter_area_id'])) {
            $query->where('area_id', $aditionalValidation['filter_area_id']);
        }

        $query = $this->find($request, $query, $searchableColumns);
        $results = $this->paginate($request, $query, $searchableColumns);
        return response()->json($results);
    }

    public function show($id)
    {
        $query = Plant::query();
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
            'area_id' => 'required|integer|exists:areas,id',
            'active' => 'boolean',
        ]);
        

        $plant = Plant::create($request->only(['name', 'display_name', 'description', 'area_id', 'active']));
        
         $plant->load('area');
        
        return response()->json(['data' => $plant], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);
        $plant = Plant::find($id);
    if (!$plant) {
        return response()->json(['message' => 'Planta no encontrada'], 404);
    }
    $plant->update($request->only(['name', 'display_name', 'description', 'active']));
    return response()->json(['data' => $plant,
        'message' => 'Planta actualizada exitosamente.'], 200);
    }

public function destroy(string $uuid)
{
     $query = Plant::query();

    $response = $this->eraseById($query, $uuid);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Planta eliminada correctamente']);
}
}

