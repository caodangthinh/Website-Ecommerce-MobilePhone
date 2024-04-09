<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    
                $token = JWTAuth::attempt([
                    "email" => $request->email,
                    "password" => $request->password
                ]);
        
                if(!empty($token)){
        
                    return response()->json([
                        "status" => true,
                        "message" => 'User Register Successfully',
                        'user' => $user,
                        "token" => $token
                    ], 200);
                }
    
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

                $token = JWTAuth::attempt([
                    "email" => $request->email,
                    "password" => $request->password
                ]);
        
                if(!empty($token)){
        
                    return response()->json([
                        "status" => true,
                        "message" => 'User Login Successfully',
                        'user' => $user,
                        "token" => $token
                    ], 200);
                }
    
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
            ], 500);
        
        }
    }
    public function refreshToken(){
        
        $newToken = auth()->refresh();

        return response()->json([
            "status" => true,
            "message" => "New access token",
            "token" => $newToken
        ]);
    }

    public function getUser(Request $request)
    {
        try{
            $userdata = auth()->user();

            return response()->json([
                "status" => true,
                "message" => "Profile data",
                "user" => $userdata
            ], 200);    
        } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
            ], 500);
        }
        
    }

    public function logout(Request $request)
    {
        $user = $request->user(); 
        if ($user) {
            $user->tokens()->delete();
            
            Auth::logout();
            
            $cookie = cookie('remember_web', null, -1);
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ])->withCookie($cookie);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No user logged in'
            ], 401);
        }
    }
}
