<?php

use Illuminate\Support\Facades\Route;

$middlewareCors = [
    'api',
    'cors',
];

$middlewareAuth = [
    'auth:api',
    'cors',
];
$middlewareBackend = array_merge($middlewareAuth, [
    //App\Middleware\BackendMiddleware::class,
    //App\Middleware\PermissionMiddleware::class,
]);
Route::any('/test', '\App\Http\Controllers\TestController@test');
