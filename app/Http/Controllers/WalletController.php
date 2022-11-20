<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;

class WalletController extends Controller
{

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
    public function deposit(Request $request)
    {

        $credentials = $request->only('amount', 'id');

        $validator = Validator::make($credentials, [
            'id' => 'required|numeric|exists:users',
            'amount' => 'required|integer|min:1',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $user = User::find($request->id);

        $user->deposit($request->amount);

        return response()->json([
            'user' => $user,
        ]);
        
    }

    public function withdraw(Request $request)
    {
        
        $credentials = $request->only('amount', 'id');

        $validator = Validator::make($credentials, [
            'id' => 'required|numeric|exists:users',
            'amount' => 'required|integer|min:1',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $user = User::find($request->id);

        try {

            $user->withdraw($request->amount);

        } catch (\Bavix\Wallet\Exceptions\InsufficientFunds $ex) {
            
            return response()->json([
                'message' => 'Insufficient Funds',
                'user' => $user,
            ], 422);

        } catch (\Bavix\Wallet\Exceptions\BalanceIsEmpty $ex){
            
            return response()->json([
                'message' => 'Insufficient Funds',
                'user' => $user,
            ], 422);

        }


        return response()->json([
            'user' => $user,
        ]);
    }
}
