<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\HttpResponses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HttpResponses;
    public function register(StoreUserRequest $request){
        $validated = $request->validated();
        $user = User::create([
            'name'=>$validated['name'],
            'email'=>$validated['email'],
            'password'=>Hash::make($validated['password'])
        ]);
        return $this->success([
            'user'=>$user,
            'access_token'=>$user->createToken('Token')->plainTextToken
        ], 'Registered Successfully');
    }

    public function login(){

    }
}
