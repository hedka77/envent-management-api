<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string'
                           ]);

        $user = User::where('email', $request->email)->first();

        if(!$user)
        {
            throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
                                                    ]);
        }

    }

    public function logout(Request $request)
    {

    }
}
