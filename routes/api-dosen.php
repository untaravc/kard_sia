<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dosen\ResidentController;
use App\Http\Controllers\Api\Dosen\ScoringController;
use App\Http\Controllers\Api\Dosen\HistoryController;

Route::group(['prefix'=>'d-rest'], function (){
   Route::get('test', [TestController::class, 'index']);
   Route::post('login', [AuthController::class, 'login']);
   Route::get('profile', [AuthController::class, 'profile']);
   Route::post('logout', [AuthController::class, 'logout']);

   //Residents
   Route::get('residents', [ResidentController::class, 'index']);
   Route::get('scoring', [ScoringController::class, 'index']);
   Route::get('histories', [HistoryController::class, 'index']);
});