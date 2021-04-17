<?php

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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::middleware('web')->group(function () {

    Route::middleware(['auth'])->group(function () {
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/message/{id}', 'HomeController@getMessage')->name('message');
        Route::post('message', 'HomeController@sendMessage');
        Route::get('historical', 'HomeController@historical');
        Route::post('room_send', 'HomeController@room_send');
    });
});
