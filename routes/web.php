<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/resetPasword', [ServicesController::class, 'UpdatePassword']);
});
*/


Route::get('/', function () {
    return view('welcome');
});

