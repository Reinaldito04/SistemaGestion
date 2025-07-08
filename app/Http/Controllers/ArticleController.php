<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use App\Models\Article;

class ArticleController extends Controller
{
 
    use ControllerTrait;


       public function __construct() {
      
        $this->middleware('permission:articles-browse', ['only' => ['index']]);
        $this->middleware('permission:articles-read', ['only' => ['show']]);
        $this->middleware('permission:articles-edit', ['only' => ['update']]);
        $this->middleware('permission:articles-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
      $aditionalValidation = $request->validate([
            'filter_active' => 'boolean',
            'filter_article_type_id' => 'integer|exists:article_types,id',
            
        ]);
        $searchableColumns = ['id', 'name', 'display_name', 'description'];
        $query = Article::query();

        if (isset($aditionalValidation['filter_active'])) {
            $query->where('active', $aditionalValidation['filter_active']);
        }

        if (isset($aditionalValidation['filter_article_type_id'])) {
            $query->where('article_type_id', $aditionalValidation['filter_article_type_id']);
        }

        $query = $this->find($request, $query, $searchableColumns);
        $results = $this->paginate($request, $query, $searchableColumns);
        return response()->json($results);
    }

    public function show($id)
    {
        $query = Article::query();
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
            'name' => 'required|string|max:255|unique:articles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'article_type_id' => 'required|integer|exists:article_types,id',
            'active' => 'boolean',
        ]);
        

        $plant = Article::create($request->only(['name', 'display_name', 'description', 'article_type_id', 'active']));
        
        
        return response()->json(['data' => $plant], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:articles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'article_type_id' => 'required|integer|exists:article_types,id',
            'active' => 'boolean',
        ]);
        $article = Article::find($id);
    if (!$article) {
        return response()->json(['message' => 'Articulo no encontrado'], 404);
    }
    $article->update($request->only(['name', 'display_name', 'description', 'active']));
    return response()->json(['data' => $article,
        'message' => 'Articulo actualizada exitosamente.'], 200);
    }

public function destroy(string $uuid)
{
     $query = Article::query();

    $response = $this->eraseById($query, $uuid);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Articulo eliminada correctamente']);
}
}
