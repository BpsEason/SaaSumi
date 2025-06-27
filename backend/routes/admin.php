<?php

use Illuminate\Support\Facades\Route;

Route::group(['domain' => 'admin.localhost', 'middleware' => 'web'], function () {
    Route::get('/', function () {
        return 'This is the central admin panel.';
    });
});
