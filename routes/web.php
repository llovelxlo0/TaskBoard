<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

@include __DIR__ . '/Task.php';
@include __DIR__ . '/Auth.php';
