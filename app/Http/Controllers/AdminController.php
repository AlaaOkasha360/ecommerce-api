<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserAdminRequest;
use App\Http\Resources\UsersResource;
use App\HttpResponses;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use HttpResponses;

    public function index(){
        return UsersResource::collection(User::all());
    }

    public function show(User $user){
        return new UsersResource($user);
    }

    public function update(UpdateUserAdminRequest $request, User $user){
        $validated = $request->validated();
        $user->update($validated);
        return new UsersResource($user);
    }

    public function destroy(User $user){
        $user->delete();
        return response()->noContent();
    }
}
