<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::any('/', function (){
    return response()->json([
        'status' => 200,
        "message" => "Welcome to API"
    ]);
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    
    Route::get('login', function() {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
        ], 401);
    })->name('login');

    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('me', 'me');
});

Route::controller(WalletController::class)->group(function () {
    Route::post('deposit', 'deposit');
    Route::post('withdraw', 'withdraw');
});

Route::controller(UserController::class)->group(function () {
    Route::get('users', 'show');
});
