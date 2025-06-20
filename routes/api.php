<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HelpDeskController;
use App\Http\Controllers\RabbitMQController;
use App\Http\Controllers\Employees\EmployeesController;
use App\Http\Controllers\Employees\DepartmentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'sanctum'], function() {
    Route::post('login', [AuthController::class, 'login']);
});






Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::group(['prefix' => 'sanctum'], function() {
    Route::get('auth', [AuthController::class, 'auth']);
    Route::get('check', [AuthController::class, 'check']);
    Route::post('logout', [AuthController::class, 'logout']);
});

    Route::group(['prefix' => 'users'], function() {

        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

   
});

