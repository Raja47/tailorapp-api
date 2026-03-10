<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
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

Route::get('/' , function (){
    return view("index");
});



Route::get('/test', function () {

    Mail::raw('SES working!', function ($message) {
        $message->to('rajexhkumar123@gmail.com')
                ->subject('Test SES Email')
                ;
    });

    return "Email sent";
});

//web.php
Route::fallback(function () {
    return abort(404); //default 404
});

