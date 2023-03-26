<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/mtn', function () {
    return view('mtn');
});

Route::get('/payment',[AuthController::class , 'emailpromot'])->name('payment');
Route::get('/code',[AuthController::class , 'mtn'])->name('code');
//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');