<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
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

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'answer' => 'required',
                'newPassword' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::where('email', $request->email)
                ->where('answer', $request->answer)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wrong Email or Answer!'
                ], 404);
            }

            $user->password = Hash::make($request->newPassword);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function userAuth(Request $request)
    {
        return response()->json(['ok' => true], 200);
    }

    public function adminAuth(Request $request)
    {
        return response()->json(['ok' => true], 200);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user(); 
    
            $name = $request->input('name');
            $password = $request->input('password');
            $address = $request->input('address');
            $phone = $request->input('phone');
    
            if ($password && strlen($password) < 6) {
                return response()->json(['error' => 'Password is required and must be at least 6 characters long'], 400);
            }
    
            $hashedPassword = $password ? Hash::make($password) : null;
    
            $user->name = $name ?: $user->name;
            $user->password = $hashedPassword ?: $user->password;
            $user->address = $address ?: $user->address;
            $user->phone = $phone ?: $user->phone;
            $user->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Profile Updated Successfully',
                'updatedUser' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error While Updating Profile',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function getOrders(Request $request)
    {
        try {
            $userId = auth()->id();

            $orders = Order::whereHas('user', function ($query) use ($userId) {
                $query->where('id', $userId);
            })->with(['products' => function ($query) {
                $query->select('products.id', 'products.name', 'products.description', 'products.price', 'products.category_id', 'products.quantity', 'products.shipping');
            }, 'user:id,name'])->get();

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while retrieving orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllOrders(Request $request)
    {
        try {
            $orders = Order::with(['products' => function ($query) {
                $query->select('products.id', 'products.name', 'products.description', 'products.price', 'products.category_id', 'products.quantity', 'products.shipping');
            }, 'user:id,name'])->get();
    
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while retrieving orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $orderId)
    {
        try {
            $status = $request->input('status');

            $order = Order::findOrFail($orderId);
            $order->status = $status;
            $order->save();

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error While Updating Order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
