<?php

use App\Http\Controllers\RoomController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\UserLastOnlineTime;


Auth::routes();

Route::middleware(['auth', UserLastOnlineTime::class])->group(function () {
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');



    Route::get('/me', function () {
        return response()->json(auth()->user());
    })->name('me');

    Route::get('/home', [HomeController::class, 'index'])
        ->name('home');

    Route::get('/messages/{roomId}', [HomeController::class, 'messages'])
        ->name('messages');

    Route::post('/message', [HomeController::class, 'message'])
        ->name('message');
    Route::get('/users/{userId}/room', [RoomController::class, 'getRoomByUser'])
        ->name('rooms.show');


    Route::get('/search', [SearchController::class, 'index']);

});
