<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\HomeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login',[LoginController::class, 'login']);
Route::post('/register',[RegisterController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/rooms', [RoomController::class, 'index']);

    Route::get('/me', function () {
        return response()->json(auth()->user());
    });


    Route::get('/messages', [HomeController::class, 'messages']);

    Route::post('/message', [HomeController::class, 'message']);
});
