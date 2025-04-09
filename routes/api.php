<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\HomeController;




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/rooms', [RoomController::class, 'index']);


    Route::get('/me', function () {
        return response()->json(auth()->user());
    });

    Route::get('/messages', [HomeController::class, 'messages']);
    Route::post('/message', [HomeController::class, 'message']);

});
