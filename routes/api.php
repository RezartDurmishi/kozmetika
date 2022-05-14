<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
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

Route::get('/product/image/{fileName}', [ProductController::class, 'displayImage']);

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
        Route::put('/user/update/{id}', [UserController::class, 'update']);
        Route::get('/user/get/{id}', [UserController::class, 'getUserById']);

        //Product
        Route::get('/product/get/{id}', [ProductController::class, 'getProductById']);
        Route::get('/product/list', [ProductController::class, 'list']);

        //Category
        Route::get('/category/list', [CategoryController::class, 'list']);
        Route::get('/category/get/{id}', [CategoryController::class, 'getCategoryById']);
        Route::post('/category/create', [CategoryController::class, 'create']);
        Route::delete('/category/delete/{id}', [CategoryController::class, 'deleteById']);
        Route::put('/category/update/{id}', [CategoryController::class, 'updateById']);

        //Order
        Route::post('/order/create', [OrderController::class, 'create']);
        Route::get('/order/list', [OrderController::class, 'list']);
        Route::get('/order/get/{id}', [OrderController::class, 'getOrderById']);
        Route::put('/order/cancel/{id}', [OrderController::class, 'cancelOrder']);
    });

//Authenticated Admin
Route::group([
    'middleware' => ['api', 'admin'],
    'namespace' => 'App\Http\Controllers'],
    function ($router) {
        //User
        Route::get('/user/list', [UserController::class, 'listUsers']);
        Route::delete('/user/delete/{id}', [UserController::class, 'deleteUserById']);

        //Product
        Route::post('/product/create', [ProductController::class, 'create']);
        Route::delete('/product/delete/{id}', [ProductController::class, 'deleteById']);
        Route::put('/product/update/{id}', [ProductController::class, 'updateById']);

        //Order
        Route::put('/order/approve/{id}', [OrderController::class, 'approveOrder']);
    });

