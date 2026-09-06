<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterUserRequest;
use App\Services\Api\V1\Auth\AuthServices;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthServices $authServices)
    {

    }
    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();
        $user = $this->authServices->register($data);
        if ($user) {
            return response()->json([
                'success' => true,
                'user' => ['id' => $user->id , 'name' => $user->name, 'email' => $user->email],
                'message' => 'registered successfully',
            ],201);
        }
        return response()->json([
            'success' => false,
            'message' => 'failed to register',
        ],400);
    }
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        return $this->authServices->login($data);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
        return $this->authServices->logout($user);
    }
}
