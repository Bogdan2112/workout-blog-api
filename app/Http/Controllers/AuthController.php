<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login succesful',
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout succesful'
        ]);
    }

    public function register(RegisterRequest  $request)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|string|min:8'
        // ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password //Hash::make($request->password)
        ]);
        
        return response()->json([
            'message' => 'User created succesfuly',
            'user' => $user
        ], 201);
    }
}
