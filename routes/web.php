<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Akses lewat browser: http://localhost:8000/test-firebase-login
Route::view('/test-firebase-login', 'login-firebase');

Route::get('/test-map', function () {
    return view('test-address');
});

Route::get('/test-checkout', function () {
    return view('checkout-test');
})->name('checkout.test');
