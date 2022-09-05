<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('admin')->group(function () {
    Route::get('/home', function () {
        // dd(auth()->user());
        // dump(auth()->guard());
        return view('home');
    })->middleware('auth:admin');
});

Route::get('/home', function () {
    // dd(auth()->user());
    // dump(auth()->guard());
    return view('home');
})->middleware('auth');


