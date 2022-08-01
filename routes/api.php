<?php

use App\Http\Controllers\AdminsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\TransactionsController;
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
    Route::prefix('admin')->group(function () {
        Route::post('/', [AdminsController::class, 'store']);
        Route::post('/login', [AdminsController::class, 'login']);
        Route::get('/', [AdminsController::class, 'showprofile'])->middleware(['auth:sanctum', 'type.admin']);
        Route::post('/logout', [AdminsController::class, 'logout'])->middleware(['auth:sanctum', 'type.admin']);

        Route::prefix('kyc')->group(function () {
            Route::post('/', [KycController::class, 'verifyBvn'])->middleware(['auth:sanctum', 'type.admin']);
        });

        Route::prefix('documents')->group(function () {
            Route::get('/', [DocumentsController::class, 'admminListDocument'])->middleware(['auth:sanctum', 'type.admin']);
            Route::get('/{id}', [DocumentsController::class, 'adminShow'])->middleware(['auth:sanctum', 'type.admin']);
            Route::get('approve/{id}', [DocumentsController::class, 'mackGood'])->middleware(['auth:sanctum', 'type.admin']);
        });

        Route::prefix('payments')->group(function () {
            Route::get('manual/verification', [PaymentsController::class, 'listUncompletedMamualPayment'])->middleware(['auth:sanctum', 'type.admin']);
            Route::post('manual/confirmation', [CustomersController::class, 'confirmPayment'])->middleware(['auth:sanctum', 'type.admin']);

        });
    });

    Route::prefix('customers')->group(function () {
        Route::post('register', [CustomersController::class, 'create']);
        Route::post('login', [CustomersController::class, 'login']);
        Route::post('forgotpassword', [CustomersController::class, 'forgotpassword']);
        Route::post('addpassword', [CustomersController::class, 'addpassword'])->middleware(['auth:sanctum', 'type.customer']);
        Route::get('profile', [CustomersController::class, 'getData'])->middleware(['auth:sanctum', 'type.customer']);
        Route::get('logout', [CustomersController::class, 'logout'])->middleware(['auth:sanctum', 'type.customer']);
        Route::get('verifypayments', [CustomersController::class, 'verifyPayments'])->middleware(['auth:sanctum', 'type.customer']);
        Route::post('manualpayments', [PaymentsController::class, 'manualpayments'])->middleware(['auth:sanctum', 'type.customer']);

        Route::post('update', [CustomersController::class, 'updateProfile'])->middleware(['auth:sanctum', 'type.customer']);

        Route::post('upload/profile/img', [CustomersController::class, 'uploadProfile'])->middleware(['auth:sanctum', 'type.customer']);
        Route::prefix('kyc')->group(function () {
            Route::post('create', [KycController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });
        Route::prefix('documents')->group(function () {
            Route::post('create', [DocumentsController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('wallet')->group(function () {
            Route::post('fund', [TransactionsController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('verify', [TransactionsController::class, 'verifyPayments'])->middleware(['auth:sanctum', 'type.customer']);
        });

    });

    Route::prefix('plans')->group(function () {
        Route::post('create', [PlansController::class, 'create']);
        Route::get('list', [PlansController::class, 'index']);
    });

});
