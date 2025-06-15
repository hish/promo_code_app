<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use App\Models\User;
use App\Enums\UserRole;

class UsersController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
            "password" => "required|confirmed",
            'role' => ['required', new Enum(UserRole::class)],
        ]);
 
        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            $response = [
                'status'  => false,
                'message' => $errorMessage,
            ];
            return response()->json($response, 422);
        }
 
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "role" => $request->role
        ]);
 
        // Response
        return response()->json([
            "status" => true,
            "message" => "User registered successfully"
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required",
            "password" => "required"
        ]);
 
        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            $response = [
                'status'  => false,
                'message' => $errorMessage,
            ];
            return response()->json($response, 422);
        }
 
        // Check user by email
        $user = User::where("email", $request->email)->first();
 
        // Check user by password
        if (!empty($user)) {
 
            if (Hash::check($request->password, $user->password)) {

                // Login is ok
                $tokenInfo = $user->createToken("Promo Code Api");
 
                $token = $tokenInfo->plainTextToken; // Token value
 
                return response()->json([
                    "status" => true,
                    "message" => "Login successful",
                    "token" => $token
                ]);
            } else {
 
                return response()->json([
                    "status" => false,
                    "message" => "Password didn't match."
                ], 422);
            }
        } else {
 
            return response()->json([
                "status" => false,
                "message" => "Invalid credentials"
            ], 422);
        }
    }

    public function profile()
    {
        $userData = auth()->user();
 
        return response()->json([
            "status" => true,
            "message" => "Profile information",
            "data" => $userData
        ]);
    }
 
    public function logout()
    {
        request()->user()->tokens()->delete();
 
        return response()->json([
            "status" => true,
            "message" => "User logged out"
        ]);
    }
}
