<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $ip = $request->ip();
        $user_agent = $request->header('User-Agent');
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = $user->createToken($user->name)->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['Message' => 'usuario no autenticado'], 401);
        }

     
        $request->user()->currentAccessToken()->delete();

        return response()->json(['Message' => 'Se ha cerrado la sesión'], 200);
    }

    public function check(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        return response()->json(['check' => !is_null($user)]);
    }

    public function auth(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            // Carga roles y permisos relacionados
            $user->load('roles');
            $user->all_permissions = $user->allPermissions()->unique('id')->values();

            $response = [
                'success' => true,
                'data' => $user
            ];

            return response()->json($response, 200);
        }

        $response = [
            'success' => false,
            'message' => 'Registro no encontrado'
        ];

        return response()->json($response, 404);
    }

}
