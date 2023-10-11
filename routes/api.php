<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TestController;
require('api-dosen.php');
Route::get('products', [TestController::class, 'products']);
Route::post('products', [TestController::class, 'store']);
//Route::post('profile', [TestController::class, 'profile']);
//Route::post('logout', [TestController::class, 'logout']);
