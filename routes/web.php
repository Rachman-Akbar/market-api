<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthTestingController;

Route::get('/', function () {
    return view('welcome');
});



// Akses lewat browser: http://localhost:8000/test-firebase-login
Route::view('/test-firebase-login', 'login-firebase');

Route::get('/test-map', function () {
    return view('test-address');
});
