<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\services\CategoriesController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\services\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'AuthLogin']);
Route::post('/register', [AuthController::class, 'CreateUser']);
Route::post('/reset', [AuthController::class, 'ResetPassword']);

Route::middleware('checkRequest')->group(function () {
    Route::post('/resetPasword', [AuthController::class, 'UpdatePassword']);
    Route::get('/logout', [AuthController::class, 'Logout']);
    Route::post('/refreshToken', [AuthController::class, 'RefreshToken']);
});

Route::prefix('products')->middleware('checkRequest')->group(function () {
    Route::get('/', [ProductController::class, 'All']);
    Route::get('/{id}', [ProductController::class, 'FindById']);
    Route::post('/', [ProductController::class, 'Register']);
});

Route::prefix('categories')->middleware('checkRequest', 'checkUserAbilities')->group(function () {
    Route::delete('/{id}', [ProductController::class, 'Delete']);
    Route::put('/{id}', [ProductController::class, 'Update']);
    Route::delete('/{id}', [CategoriesController::class, 'Delete']);
    Route::get('/', [CategoriesController::class, 'All']);
    Route::post('/', [CategoriesController::class, 'Register']);
    Route::put('/{id}', [CategoriesController::class, 'Update']);
    Route::get('/{id}', [CategoriesController::class, 'FindById']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
