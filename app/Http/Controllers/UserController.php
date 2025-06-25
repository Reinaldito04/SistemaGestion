<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ControllerTrait;
use App\Rules\PasswordValidationRule;

class UserController extends Controller
{

    use ControllerTrait;
  
      public function __construct() {
      
        $this->middleware('permission:users-browse', ['only' => ['index']]);
        $this->middleware('permission:users-read', ['only' => ['show']]);
        $this->middleware('permission:users-edit', ['only' => ['update']]);
        $this->middleware('permission:users-add', ['only' => ['store']]);
        $this->middleware('permission:users-delete', ['only' => ['destroy']]);
        $this->middleware('permission:roles-add', ['only' => ['assignRoles']]);
        $this->middleware('permission:roles-add', ['only' => ['revokeRoles']]);
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

  public function index(Request $request)
{
    $searchableColumns = ['id', 'name', 'email'];
      $query = User::query()
        ->whereDoesntHave('roles', function($q) {
            $q->where('name', 'superadministrador'); // O el slug/nombre exacto en tu tabla de roles
        });

    // Cargar roles para evitar problemas de N+1
    $query->with('roles');
    $query = $this->find($request, $query, $searchableColumns);
    $results = $this->paginate($request, $query, $searchableColumns);
    return response()->json($results);
}
public function store(Request $request)
{
    // Validar los datos del request
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'role_id' => 'nullable|integer|exists:roles,id',
        'password' => ['required', new PasswordValidationRule],
        'password_confirmation' => ['required', 'same:password']
    ], [
        'email.unique' => 'El correo electrónico ya está en uso',
        'password_confirmation.same' => 'Las contraseñas no coinciden'
    ]);

    // Crear el usuario
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // Si se proporcionó role_id, asignar ese rol al usuario
    if ($request->filled('role_id')) {
        $role = Role::where('id', $request->role_id)->first();
        $user->roles()->attach($role);
    }
       $user->load('roles');

       return response()->json(
        ['data' => $user,
        'message'=>'Usuario creado exitosamente']
    );
}

public function update(Request $request, $uuid)
{

    $query=User::query();
    $user = $this->retrieveByid($query,$uuid);

    // Validar los datos del request
    $rules = [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
        'password_confirmation' => 'required_with:password',
        'password' => ['sometimes', 'required', new PasswordValidationRule, 'confirmed'],
    
    ];

    $messages = [
        'email.unique' => 'El correo electrónico ya está en uso',
        'password.confirmed' => 'Las contraseñas no coinciden',
    ];

    $request->validate($rules, $messages);

    // Actualizar el usuario
    $data = $request->only('name', 'email', 'password');
    foreach ($data as $key => $value) {
        if (isset($value)) {
            if ($key == 'password') {
                $user->$key = bcrypt($value);
            } else {
                $user->$key = $value;
            }
        }
    }
    $user->save();

    return response()->json(
        ['data' => $user,
        'message'=>'Usuario actualizado exitosamente']
    );
}





public function destroy(string $uuid)
{
     $query = User::query()
        ->whereDoesntHave('roles', function($q) {
            $q->where('name', 'superadministrador'); // O el slug/nombre exacto en tu tabla de roles
        });

    $response = $this->eraseById($query, $uuid);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Usuario eliminado correctamente']);
}



public function assignRoles(Request $request, $userId)
{
    $request->validate([
        'roles' => 'required|array|min:1',
        'roles.*' => 'integer|exists:roles,id',
    ]);

    $user = User::whereDoesntHave('roles', function($q) {
        $q->where('name', 'superadministrador');
    })->where('id', $userId)->first();

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    // No permitir asignar el rol superadministrador por seguridad.
    $roles = Role::whereIn('id', $request->roles)
        ->where('name', '!=', 'superadministrador')
        ->get();

    $already = [];
    $toAssign = [];
    foreach ($roles as $role) {
        if ($user->hasRole($role->name)) { // Laratrust recomienda usar el nombre
            $already[] = $role->id;
        } else {
            $toAssign[] = $role->id;
        }
    }
    if (!empty($toAssign)) {
        // Mejor práctica Laratrust:
        $user->syncRolesWithoutDetaching($toAssign);
    }

    return response()->json([
        'assigned' => $toAssign,
        'already_had' => $already,
        'message' => 'Roles procesados exitosamente.'
    ]);
}

/**
 * Revoca uno o varios roles a un usuario.
 */
public function revokeRoles(Request $request, $userId)
{
    $request->validate([
        'roles' => 'required|array|min:1',
        'roles.*' => 'integer|exists:roles,id',
    ]);

   $user = User::whereDoesntHave('roles', function($q) {
        $q->where('name', 'superadministrador');
    })->where('id', $userId)->first();

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }
    // No permitir revocar el rol superadministrador por seguridad.
    $roles = Role::whereIn('id', $request->roles)
        ->where('name', '!=', 'superadministrador')
        ->get();

    $revoked = [];
    $not_had = [];
    foreach ($roles as $role) {
        if ($user->hasRole($role->name)) {
            $revoked[] = $role->id;
        } else {
            $not_had[] = $role->id;
        }
    }

    if (!empty($revoked)) {
        
        $user->removeRoles($revoked);
        
         // O revokeRole según tu versión Laratrust
    }
   
    return response()->json([
        'revoked' => $revoked,
        'not_had' => $not_had,
        'message' => 'Roles procesados exitosamente.'
    ]);
}


}
