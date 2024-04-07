<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try{
            $validateUser = Validator::make($request->all(), 
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                    'phone' => 'required',
                    'address' => 'required',
                    'answer' => 'required'
                ]);
                $existingUser = User::where('email', $request['email'])->first();
                if ($existingUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email already registered!'
                    ], 200);
                }
                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUser->errors()
                    ], 401);
                }
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'answer' => $request->answer
                ]);
    
                return response()->json([
                    'success' => true,
                    'message' => 'User Register Successfully',
                    'user' => $user,
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ], 200);
    
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
            ], 500);
        
        }
    }

    public function login(Request $request)
    {
        try{
            $validateUser = Validator::make($request->all(), 
                [
                    'email' => 'required|email',
                    'password' => 'required',
                ]);
                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUser->errors()
                    ], 401);
                }
                if(!Auth::attempt($request->only(['email', 'password']))){
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid Email or Password!',
                    ], 401);
                }
                $user = User::where('email', $request->email)->first();
    
                return response()->json([
                    'success' => true,
                    'message' => 'User Login Successfully',
                    'user' => $user,
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ], 200);
    
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
            ], 500);
        
        }
    }
}
