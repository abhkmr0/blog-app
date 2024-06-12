<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function register(Request $request)
    {

        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create a new user instance
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Generate a token for the user
        $token = $user->createToken('API Token')->plainTextToken;

        // Return a response with the user data and token
        return response()->json([
            'status'=>true,
            'user' => $user,
            'token' => $token,
        ], 201);
    }



    public function login(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to log in the user
        if (Auth::attempt($validatedData)) {
            // If authentication succeeds, generate a token for the user
            $user = Auth::user();
            $token = $user->createToken('API Token')->plainTextToken;

            // Return a response with the user data and token
            return response()->json([
                'status' => true,
                'message' => 'User login successfully',
                'token' => $token,
                'user' => $user,
            ], 200);
        } else {
            // If authentication fails, return a response with an error message
            return response()->json([
            'status' => false,
            'message' => 'The provided credentials are incorrect.'
        ], 401);
        }
    }


    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully'], 200);
        } else {
            return response()->json(['message' => 'No authenticated user found.'], 401);
        }
    }
}
