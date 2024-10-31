<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'dni' => $request->dni,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('user');

        return ApiResponseHelper::sendResponse(null, 'Usuario registrado con éxito', 201);
    }

    public function login(LoginUserRequest $request)
    {
        // Buscar usuario por DNI
        $user = User::where('dni', $request->dni)->first();

        // Verificar si el usuario existe
        if (!$user) {
            return ApiResponseHelper::sendError('Credenciales inválidas', 401);
        }

        // Verificar la contraseña
        if (!Hash::check($request->password, $user->password)) {
            return ApiResponseHelper::sendError('Credenciales inválidas', 401);
        }

        // Eliminar tokens anteriores (opcional)
        $user->tokens()->delete();

        // Crear nuevo token
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponseHelper::sendResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 'Autenticación exitosa');
    }

    public function me(Request $request)
    {
        // El usuario ya está autenticado por el middleware auth:sanctum
        return ApiResponseHelper::sendResponse($request->user(), 'Usuario autenticado');
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return ApiResponseHelper::sendResponse(null, 'Sesión cerrada con éxito');
    }
}
