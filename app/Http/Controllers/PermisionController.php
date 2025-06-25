<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;

class PermisionController extends Controller
{
   use ControllerTrait;


    public function __construct() {

        $this->middleware('permission:permissions-browse', ['only' => ['index']]);
        $this->middleware('permission:permissions-read', ['only' => ['show']]);

    }

     public function index(Request $request)
{
    $searchableColumns = ['id', 'name', 'display_name', 'description'];
      $query = Permission::query();
      // Excluir los permisos de eliminar, editar y crear permisos
    $query->whereNotIn('name', ['permissions-delete', 'permissions-edit', 'permissions-add']);
    $query = $this->find($request, $query, $searchableColumns);
    $results = $this->paginate($request, $query, $searchableColumns);
    return response()->json($results);
}

public function show($id)
{
   $query = Permission::query();
      // Excluir los permisos de eliminar, editar y crear permisos
    $query->whereNotIn('name', ['permissions-delete', 'permissions-edit', 'permissions-add']);
    try {
        $data = $this->retrieveById($query, $id);
        return response()->json(['data' => $data->toArray()]);
    } catch (\InvalidArgumentException $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 404);
    }
}


}
