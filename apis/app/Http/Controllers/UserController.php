<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::all();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving users'], 500);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'gender' => 'required|string|max:255',
            'age' => 'required|integer|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'age' => $request->age
            ]);

            if (!$user) {
                return response()->json(['error' => 'Error creating user'], 500);
            }

            // Asignar automáticamente el rol de usuario
            $role = Role::where('name', 'Usuario')->first();
            $user->roles()->attach($role);

            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating user', 'message' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Obtener los campos actualizados de la solicitud
            $updatedFields = $request->only(['name', 'email', 'password']);

            // Filtrar los campos actualizados para eliminar los valores nulos
            $filteredFields = array_filter($updatedFields, function ($value) {
                return !is_null($value);
            });

            // Actualizar los campos en la base de datos si se proporcionaron en la solicitud
            $user->update($filteredFields);

            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating user'], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting user'], 500);
        }
    }
}
