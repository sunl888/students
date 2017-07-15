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

Route::get('/', function () {
    return view('welcome');
});

Route::get('xsc','IndexController@getStudentInfo');
Route::get('xsc_1','IndexController@studentNumGenerate')->name("xsc_1");
Route::get('jwt','LoginWapJWTController@login2Jwc');