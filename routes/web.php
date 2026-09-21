<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/reverb-test', 'reverb-test');
Route::view('/chats', 'chats');

require __DIR__.'/api.php';
