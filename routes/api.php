<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\services\ServicesController;

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

Route::middleware('CheckForAnyAbility')->group(function () {
    Route::post('/resetPasword', [ServicesController::class, 'UpdatePassword']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
