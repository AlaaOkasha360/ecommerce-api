<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\HttpResponses;
use App\Models\User;
use App\Notifications\VerifyEmailApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    use HttpResponses;
    public function register(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number']
        ]);

        $user->notify(new VerifyEmailApi());
        
        $token = Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']]);
        return $this->success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], 'Registered Successfully. Please verify your email', 201);
    }

    public function login(LoginUserRequest $request)
    {
        $validated = $request->validated();

        if (!$token = Auth::attempt($validated)) {
            return $this->error([], "Credentials do not match", 401);
        }
        return $this->success([
            'user' => Auth::guard()->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], 'Logged in successfully');
    }

    public function logout()
    {
        Auth::logout();
        return $this->success([], 'Logged out successfully');
    }

    public function me(Request $request)
    {
        return $this->success([
            'user' => Auth::user(),
        ]);
    }

    public function refresh()
    {
        $token = Auth::refresh();

        return $this->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], 'Token refreshed successfully');
    }
}
