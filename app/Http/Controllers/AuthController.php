<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\HttpResponses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HttpResponses;
    public function register(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);
        return $this->success([
            'user' => $user,
            'access_token' => $user->createToken('Token')->plainTextToken
        ], 'Registered Successfully', 201);
    }

    public function login(LoginUserRequest $request)
    {
        $validated = $request->validated();
        if (!Auth::attempt($validated)) {
            return $this->error([], "Credentials do not match", 401);
        }
        $user = User::where('email', $validated['email'])->first();
        return $this->success(['user' => $user, 'access_token' => $user->createToken('Token')->plainTextToken]);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->success([], 'Logged out successfully');
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        return $this->success([
            'user' => $user,
        ]);
    }
}
