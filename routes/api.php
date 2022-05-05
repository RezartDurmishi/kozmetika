<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
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

//Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/homepage', function () {
    return 'HOMEPAGE';
})->name('homepage');

Route::get('/admin-permission-needed', function () {
    return response()->json(['error' => 'Only admin can access this resource.'], 401);
});

//Authenticated User
Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers'],
    function ($router) {
        //Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refresh']);
        Route::post('/get-logged-user', [AuthController::class, 'getLoggedUser']);

        //User
        Route::put('/user/reset-password/{id}', [UserController::class, 'resetPassword']);
        Route::get('/user/get/{id}', [UserController::class, 'getUserById']);

        //Product
        Route::post('/product/create', [ProductController::class, 'create']);
    });

//Authenticated Admin
Route::group([
    'middleware' => ['api', 'admin'],
    'namespace' => 'App\Http\Controllers'],
    function ($router) {
        //User
        Route::get('/user/list', [UserController::class, 'listUsers']);
        Route::delete('/user/delete/{id}', [UserController::class, 'deleteUserById']);
    });

