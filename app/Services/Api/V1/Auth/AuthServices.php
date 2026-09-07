<?php

namespace App\Services\Api\V1\Auth;


use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }
    public function register(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

    }
    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->firstOrFail();
        $credentials = $data;
            if (Auth::attempt($credentials)) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'success' => true,
                    'message' => 'logged in successfully',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],200);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ],401);
    }
    public function logout($user)
    {
        $tokenCount = $user->tokens()->count();

        if ($tokenCount > 0) {
            $user->tokens()->delete();
            return response()->json([
                'success' => true,
                'message' => 'logged out successfully',
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'No active sessions found',
        ], 401);
    }
}
