<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () { return view('welcome'); });

Auth::routes();

Route::get('/me', function () {
    return response()->json(auth()->user());
})->name('me');

Route::get('/home', [HomeController::class, 'index'])
    ->name('home');

Route::get('/messages', [HomeController::class, 'messages'])
    ->name('messages');

Route::post('/message', [HomeController::class, 'message'])
    ->name('message');



