<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers'],

    function ($router) {
        //Auth
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refresh']);
        Route::post('/get-logged-user', [AuthController::class, 'getLoggedUser']);

        //User
        Route::get('/user/list', [UserController::class, 'listUsers']);
        Route::get('/user/get/{id}', [UserController::class, 'getUserById']);
        Route::delete('/user/delete/{id}', [UserController::class, 'deleteUserById']);

    });

Route::get('/greeting', function () {
    return 'Hello World';
});

