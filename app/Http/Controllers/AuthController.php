<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function sendResponse($data, $message, $status = 200) 
    {
        $response = [
            'data' => $data,
            'message' => $message
        ];

        return response()->json($response, $status);
    }

    public function sendError($errorData, $message, $status = 500)
    {
        $response = [];
        $response['message'] = $message;
        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }

        return response()->json($response, $status);
    }

    public function getUserData($user){
        $userData = [
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "phone" => $user->phone,
            "email_verified" => empty($user->email_verified_at) ? false : true,
            "phone_verified" => empty($user->phone_verified_at) ? false : true,
            "created_at" => $user->created_at,
            "updated_at" => $user->updated_at,
        ];

        return $userData;
    }

    public function login(Request $request)
    {

        $credentials = $request->only('phone', 'password');

        $validator = Validator::make($credentials, [
            'phone' => 'required|numeric|digits:10',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
                'status' => 'success',
                'user' => $this->getUserData($user),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ],
                'wallet' => [
                    'balance' => $user->balanceInt
                ],
            ]);

    }

    public function register(Request $request){

        $credentials = $request->only('name', 'phone', 'password', 'c_password');

        $validator = Validator::make($credentials, [
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits:10|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $this->getUserData($user),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
            'wallet' => [
                'balance' => $user->balanceInt
            ],
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function me()
    {
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'user' => $this->getUserData($user),
            'wallet' => [
                'balance' => $user->balanceInt
            ],
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}
