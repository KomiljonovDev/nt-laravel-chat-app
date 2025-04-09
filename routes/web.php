<?php
//
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\UserLastOnlineTime;
//

Auth::routes();

Route::middleware(['auth', UserLastOnlineTime::class])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');

    Route::get('/home', [HomeController::class, 'index'])
        ->name('home');

});
