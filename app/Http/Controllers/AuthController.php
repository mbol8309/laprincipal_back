<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $user = User::where('email',$request->username)->first();
        if (!$user || !Hash::check($request->password,$user->password)){
            throw ValidationException::withMessages([
                'username'=>['Credentials invalid']
            ]);
        }
        $user->token = $user->createToken('auth_token')->plainTextToken;

        return new UserResource($user);
    }
}
