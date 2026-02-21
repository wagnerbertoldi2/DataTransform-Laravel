<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'aplicacao' => 'DataTransform API',
        'versao' => '1.0',
        'status' => 'online',
    ]);
});
