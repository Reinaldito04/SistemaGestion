<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use Laratrust\Models\Permission;

class RoleController extends Controller
{
    use ControllerTrait;


       public function __construct() {
      
        $this->middleware('permission:roles-browse', ['only' => ['index']]);
        $this->middleware('permission:roles-read', ['only' => ['show']]);
        $this->middleware('permission:roles-edit', ['only' => ['update']]);
        $this->middleware('permission:roles-delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
        $searchableColumns = ['id', 'name', 'display_name', 'description'];
        $query = Role::query();
        $query->whereNotIn('name', ['superadministrador']); 

        $query = $this->find($request, $query, $searchableColumns);
        $results = $this->paginate($request, $query, $searchableColumns);
        return response()->json($results);
    }

    public function show($id)
    {
        $query = Role::query();
        $query->whereNotIn('name', ['superadministrador']);
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
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::create($request->only(['name', 'display_name', 'description']));
        return response()->json(['data' => $role], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);
        $role = Role::whereNotIn('name', ['superadministrador'])->find($id);
    if (!$role) {
        return response()->json(['message' => 'Rol no encontrado'], 404);
    }           
    $role->update($request->only(['name', 'display_name', 'description'])); 
    return response()->json(['data' => $role,
        'message' => 'Rol actualizado exitosamente.'], 200);
    }

   public function assignPermissions(Request $request, $roleId)
{
    $request->validate([
        'permissions' => 'required|array|min:1',
        'permissions.*' => 'integer|exists:permissions,id',
    ]);

    $role = Role::whereNotIn('name', ['superadministrador'])->where('id', $roleId)->first();
    if (!$role) {
        return response()->json(['message' => 'Rol no encontrado'], 404);
    }



    $permissions = \App\Models\Permission::whereIn('id', $request->permissions)->get();

    $assigned = [];
    $already = [];
    foreach ($permissions as $permission) {
        if ($role->hasPermission($permission->name)) {
            $already[] = $permission->id;
        } else {
            $role->givePermission($permission);
            $assigned[] = $permission->id;
        }
    }

    return response()->json([
        'assigned' => $assigned,
        'already_had' => $already,
        'message' => 'Permisos procesados exitosamente.'
    ]);
}

public function revokePermissions(Request $request, $roleId)
{
    $request->validate([
        'permissions' => 'required|array|min:1',
        'permissions.*' => 'integer|exists:permissions,id',
    ]);

     $role = Role::whereNotIn('name', ['superadministrador'])->where('id', $roleId)->first();
    if (!$role) {
        return response()->json(['message' => 'Rol no encontrado'], 404);
    }

    $permissions = \App\Models\Permission::whereIn('id', $request->permissions)->get();

    $revoked = [];
    $not_had = [];
    foreach ($permissions as $permission) {
        if ($role->hasPermission($permission->name)) {
            $role->removePermission($permission);
            $revoked[] = $permission->id;
        } else {
            $not_had[] = $permission->id;
        }
    }

    return response()->json([
        'revoked' => $revoked,
        'not_had' => $not_had,
        'message' => 'Permisos procesados exitosamente.'
    ]);
}
}