<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @author Fahed
 * @description Handles user authentication operations including registration, login, and logout
 * @package App\Http\Controllers\API
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param RegisterRequest $request Validated registration data
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 201 {
     *   "user": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "john@example.com",
     *     "created_at": "2024-03-10T15:30:00.000000Z"
     *   },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1..."
     * }
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Authenticate user and create token.
     *
     * @param LoginRequest $request Validated login credentials
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "john@example.com"
     *   },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1..."
     * }
     * 
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(LoginRequest $request)
    {
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Revoke the token that was used to authenticate the current request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "message": "Successfully logged out"
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
