<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HelpDeskController;
use App\Http\Controllers\RabbitMQController;
use App\Http\Controllers\PermisionController;
use App\Http\Controllers\DepartmentController;

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
        Route::post('/{id}/assign-roles', [UserController::class, 'assignRoles']);
        Route::post('/{id}/revoke-roles', [UserController::class, 'revokeRoles']);
        Route::post('/{id}/assign-permissions', [UserController::class, 'assignPermissions']);
        Route::post('/{id}/revoke-permissions', [UserController::class, 'revokePermissions']);
    });


       Route::group(['prefix' => 'permissions'], function() {

        Route::get('/', [PermisionController::class, 'index']);
        Route::get('/{id}', [PermisionController::class, 'show']);
    });

      Route::group(['prefix' => 'roles'], function() {

        Route::get('/', [RoleController::class, 'index']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::post('/{roleId}/assign-permissions', [RoleController::class, 'assignPermissions']);
        Route::post('/{roleId}/revoke-permissions', [RoleController::class, 'revokePermissions']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);

    });

    Route::group(['prefix' => 'departments'], function() {

        Route::get('/', [DepartmentController::class, 'index']);
        Route::get('/{id}', [DepartmentController::class, 'show']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::put('/{id}', [DepartmentController::class, 'update']);
        Route::delete('/{id}', [DepartmentController::class, 'destroy']);
    });
    Route::group(['prefix' => 'areas'], function() {

        Route::get('/', [AreaController::class, 'index']);
        Route::get('/{id}', [AreaController::class, 'show']);
        Route::post('/', [AreaController::class, 'store']);
        Route::put('/{id}', [AreaController::class, 'update']);
        Route::delete('/{id}', [AreaController::class, 'destroy']);
    });
    Route::group(['prefix' => 'plants'], function() {

        Route::get('/', [PlantController::class, 'index']);
        Route::get('/{id}', [PlantController::class, 'show']);
        Route::post('/', [PlantController::class, 'store']);
        Route::put('/{id}', [PlantController::class, 'update']);
        Route::delete('/{id}', [PlantController::class, 'destroy']);
    });
        Route::group(['prefix' => 'sectors'], function() {

        Route::get('/', [SectorController::class, 'index']);
        Route::get('/{id}', [SectorController::class, 'show']);
        Route::post('/', [SectorController::class, 'store']);
        Route::put('/{id}', [SectorController::class, 'update']);
        Route::delete('/{id}', [SectorController::class, 'destroy']);
    });
    



   
});

