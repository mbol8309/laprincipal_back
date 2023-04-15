<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
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
    Route::post('/getByIds',[GenericController::class,'getByIds']);
    Route::post('/updateById',[GenericController::class,'updateById']);
    Route::post('/insert',[GenericController::class,'insert']);
    Route::post('/delete',[GenericController::class,'delete']);
    Route::post('/action',[GenericController::class,'action']);

    Route::prefix('/file')->group(function(){
        Route::post('/upload', [FileController::class,'upload']);
        Route::post('/getAll', [FileController::class,'getAll']);
    });
});

Route::get('/front/{item}',[FrontController::class,'show']);
Route::get('/front',[FrontController::class,'index']);
