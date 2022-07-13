<?php

use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\PlansController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::prefix('customers')->group(function () {
        Route::post('register', [CustomersController::class, 'create']);
        Route::post('login', [CustomersController::class, 'login']);
        Route::get('profile', [CustomersController::class, 'getData'])->middleware(['auth:sanctum', 'type.customer']);
        Route::get('logout', [CustomersController::class, 'logout'])->middleware(['auth:sanctum', 'type.customer']);
        Route::get('verifypayments', [CustomersController::class, 'verifyPayments']);
        Route::post('upload/profile/img', [CustomersController::class, 'uploadProfile'])->middleware(['auth:sanctum', 'type.customer']);

        Route::prefix('kyc')->group(function () {
            Route::post('create', [KycController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('documents')->group(function () {
            Route::post('create', [DocumentsController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

    });

    Route::prefix('plans')->group(function () {
        Route::post('create', [PlansController::class, 'create']);
        Route::get('list', [PlansController::class, 'index']);

    });

});
