<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    public function show($id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'gender' => $user->gender,
            'age' => $user->age
        ];
    
        return response()->json(['data' => $userData], 200);
    }
}
