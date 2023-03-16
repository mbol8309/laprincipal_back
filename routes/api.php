<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\SettingsController;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/auth')->group(function(){
    Route::post('/login',[AuthController::class,'login']);
});

Route::middleware('auth:sanctum')->group(function(){

    Route::prefix('/setting')->group(function(){
        Route::get('/',[SettingsController::class,'groups']);
        Route::get('/{group}',[SettingsController::class,'items'])->whereAlpha('group');
        Route::post('/{group}',[SettingsController::class,'store_items'])->whereAlpha('group');
    });


    Route::post('/getAll',[GenericController::class,'getAll']);
    Route::post('/getById',[GenericController::class,'getById']);
});

Route::get('/front/{item}',[FrontController::class,'show']);
