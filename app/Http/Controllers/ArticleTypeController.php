<?php

namespace App\Http\Controllers;

use App\Models\ArticleType;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;

class ArticleTypeController extends Controller
{
      
    use ControllerTrait;


       public function __construct() {
      
        $this->middleware('permission:article_types-browse', ['only' => ['index']]);
        $this->middleware('permission:article_types-read', ['only' => ['show']]);
        $this->middleware('permission:article_types-edit', ['only' => ['update']]);
        $this->middleware('permission:article_types-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
      $aditionalValidation = $request->validate([
            'filter_active' => 'boolean',
          
        ]);
        $searchableColumns = ['id', 'name', 'display_name', 'description'];
        $query = ArticleType::query();

        if (isset($aditionalValidation['filter_active'])) {
            $query->where('active', $aditionalValidation['filter_active']);
        }

        $query = $this->find($request, $query, $searchableColumns);
        $results = $this->paginate($request, $query, $searchableColumns);
        return response()->json($results);
    }

    public function show($id)
    {
        $query = ArticleType::query();
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
            'name' => 'required|string|max:255|unique:article_types,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $area = ArticleType::create($request->only(['name', 'display_name', 'description', 'active']));
        return response()->json(['data' => $area], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:article_types,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);
        $area = ArticleType::find($id);
    if (!$area) {
        return response()->json(['message' => 'Área no encontrada'], 404);
    }           
    $area->update($request->only(['name', 'display_name', 'description', 'active'])); 
    return response()->json(['data' => $area,
        'message' => 'Tipo de artículo actualizado exitosamente.'], 200);
    }

public function destroy(string $uuid)
{
     $query = ArticleType::query();

    $response = $this->eraseById($query, $uuid);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Tipo de artículo eliminado correctamente']);
}

}
