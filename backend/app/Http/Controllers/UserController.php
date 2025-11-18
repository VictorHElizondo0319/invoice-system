<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Search users by q param (name or email)
     */
    public function index(Request $request)
    {
 
        $users = User::where('role', 'user')->orderBy('name')->select(['id', 'name', 'email'])->get();

        return response()->json($users);
    }

    /**
     * Create a new user (admin-only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : Hash::make(str()->random(12)),
            'role' => 'user',
        ]);

        return response()->json($user, 201);
    }
}
