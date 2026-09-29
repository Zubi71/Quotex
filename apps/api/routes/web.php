<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name'    => config('app.name'),
        'version' => '1.0.0',
        'status'  => 'operational',
    ]);
});
