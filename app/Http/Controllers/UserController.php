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
  

    public function getUsers()
    {
        $roles = Role::all();
        return view('users', compact('roles'));
    }
 
    public function show(string $uuid)
    {
        $query = User::query();
        $data = $this->retrieveByUuid($query, $uuid);
        return response()->json(['data' => $data]);
    }



  public function index(Request $request)
{
    $searchableColumns = ['id', 'name', 'email'];
    $query = User::query();
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
        'role_uuid' => 'nullable|uuid|exists:roles,uuid', 
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

    // Si se proporcionó role_uuid, asignar ese rol al usuario
    if ($request->filled('role_uuid')) {
        $role = Role::where('uuid', $request->role_uuid)->first();
        $user->roles()->attach($role);
    }

    return response()->json($user, 201);
}

public function update(Request $request, $uuid)
{

    $query=User::query();
    $user = $this->retrieveByUuid($query,$uuid);

    // Validar los datos del request
    $rules = [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
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

    return response()->json($user, 200);
}





public function destroy(string $uuid)
{
    $query = User::query();
    $response = $this->eraseByUuid($query, $uuid);

    if ($response->getStatusCode() != 200) {
        return $response;
    }

    return response()->json(['message' => 'Usuario eliminado correctamente']);
}
}
