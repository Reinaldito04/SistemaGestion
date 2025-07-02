<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use Laratrust\Models\Permission;
use App\Models\Sector;

class SectorController extends Controller
{
    
    use ControllerTrait;


       public function __construct() {

        $this->middleware('permission:sectors-browse', ['only' => ['index']]);
        $this->middleware('permission:sectors-read', ['only' => ['show']]);
        $this->middleware('permission:sectors-edit', ['only' => ['update']]);
        $this->middleware('permission:sectors-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
      $aditionalValidation = $request->validate([
            'filter_active' => 'boolean',
            'filter_plant_id' => 'integer|exists:areas,id',
            'filter_area_id' => 'integer|exists:areas,id',
          
        ]);
        $searchableColumns = ['id', 'name', 'display_name', 'description'];
        $query = Sector::query();

        if (isset($aditionalValidation['filter_active'])) {
            $query->where('active', $aditionalValidation['filter_active']);
        }

        if (isset($aditionalValidation['filter_plant_id'])) {
            $query->where('plant_id', $aditionalValidation['filter_plant_id']);
        }

        if (isset($aditionalValidation['filter_area_id'])) {
            $query->whereHas('plant', function ($q) use ($aditionalValidation) {
                $q->where('area_id', $aditionalValidation['filter_area_id']);
            });
        }

        $query = $this->find($request, $query, $searchableColumns);
        $results = $this->paginate($request, $query, $searchableColumns);
        return response()->json($results);
    }

    public function show($id)
    {
        $query = Sector::query();
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
            'name' => 'required|string|max:255|unique:sectors,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'plant_id' => 'required|integer|exists:plants,id',
            'active' => 'boolean',
        ]);
        

        $plant = Sector::create($request->only(['name', 'display_name', 'description', 'plant_id', 'active']));
        
        
        return response()->json(['data' => $plant], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sectors,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);
        $plant = Sector::find($id);
    if (!$plant) {
        return response()->json(['message' => 'Sector no encontrado'], 404);
    }
    $plant->update($request->only(['name', 'display_name', 'description', 'active']));
    return response()->json(['data' => $plant,
        'message' => 'Sector actualizado exitosamente.'], 200);
    }

public function destroy(string $uuid)
{
     $query = Sector::query();

    $response = $this->eraseById($query, $uuid);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Sector eliminado correctamente']);
}
}
