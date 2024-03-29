<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClaimAssigneeController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\FaqsController;
use App\Http\Controllers\HelpCenterController;
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
    Route::post('/sss', function () {
        return 2321;
    });

    Route::prefix('admin')->group(function () {
        Route::post('/', [AdminController::class, 'store']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::delete('/{id}', [AdminController::class, 'destroy']);

        Route::put('/{id}', [AdminController::class, 'update'])->middleware(['auth:sanctum', 'type.admin']);
        Route::post('/login', [AdminController::class, 'login']);
        Route::get('/show/profile', [AdminController::class, 'showprofile'])->middleware(['auth:sanctum', 'type.admin']);
        Route::get('', [AdminController::class, 'index']);
        Route::post('/logout', [AdminController::class, 'logout'])->middleware(['auth:sanctum', 'type.admin']);
        Route::get('/dashboard/app', [AdminController::class, 'dashboard'])->middleware(['auth:sanctum', 'type.admin']);
        Route::prefix('kyc')->group(function () {
            Route::post('/', [KycController::class, 'verifyBvn'])->middleware(['auth:sanctum', 'type.admin']);
        });

        Route::prefix('claims')->group(function () {
            Route::post('create', [ClaimController::class, 'create'])->middleware(['auth:sanctum', 'type.admin']);
            Route::put('edit', [ClaimController::class, 'edit'])->middleware(['auth:sanctum', 'type.admin']);

            Route::delete('delete/{claimid}', [ClaimController::class, 'delete'])->middleware(['auth:sanctum', 'type.admin']);

            Route::get('/list', [ClaimController::class, 'index'])->middleware(['auth:sanctum', 'type.admin']);

            Route::prefix('assignment')->group(function () {
                Route::post('create', [ClaimAssigneeController::class, 'create'])->middleware(['auth:sanctum', 'type.admin']);
                Route::get('/list', [ClaimAssigneeController::class, 'index'])->middleware(['auth:sanctum', 'type.admin']);
                Route::get('/customerlist', [ClaimAssigneeController::class, 'indexCustomer'])->middleware(['auth:sanctum', 'type.customer']);
                Route::post('/change-status', [ClaimAssigneeController::class, 'changeStatusAdmin'])->middleware(['auth:sanctum', 'type.admin']);

                Route::post('/change-status/customer', [ClaimAssigneeController::class, 'changeStatusCustomer'])->middleware(['auth:sanctum', 'type.customer']);

            });
        });

        Route::prefix('kyc')->group(function () {
            Route::post('update', [KycController::class, 'create1'])->middleware(['auth:sanctum', 'type.admin']);
        });

        Route::get('not', [FaqsController::class, function () {
            return 123456789;
        }]);

        Route::prefix('faq')->group(function () {
            Route::post('', [FaqsController::class, 'create'])->middleware(['auth:sanctum', 'type.admin']);
            Route::get('/list', [FaqsController::class, 'index']);
            Route::get('{id}', [FaqsController::class, 'show'])->middleware(['auth:sanctum', 'type.admin']);
            Route::delete('{id}', [FaqsController::class, 'destroy'])->middleware(['auth:sanctum', 'type.admin']);
            Route::post('edit', [FaqsController::class, 'edit'])->middleware(['auth:sanctum', 'type.admin']);
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

        Route::prefix('customers')->group(function () {
            Route::get('/list', [CustomersController::class, 'index'])->middleware(['auth:sanctum', 'type.admin']);
            Route::get('{id}', [CustomersController::class, 'viewOne'])->middleware(['auth:sanctum', 'type.admin']);
            Route::post('/update', [CustomersController::class, 'updateProfile1'])->middleware(['auth:sanctum', 'type.admin']);
        });

        Route::prefix('wallet')->group(function () {
            Route::get('', [AdminController::class, 'getWalletLimit'])->middleware(['auth:sanctum', 'type.admin']);
            Route::post('', [AdminController::class, 'updateWalletLimit'])->middleware(['auth:sanctum', 'type.admin']);

        });
        Route::prefix('transactions')->group(function () {
            Route::get('/list', [TransactionsController::class, 'adminList'])->middleware(['auth:sanctum', 'type.admin']);

        });
    });

    Route::prefix('customers')->group(function () {

        Route::get('list/downliners', [CustomersController::class, 'loaDowlainers'])->middleware(['auth:sanctum', 'type.customer']);

        Route::get('list/notification', [CustomersController::class, 'listNotification'])->middleware(['auth:sanctum', 'type.customer']);

        Route::get('list/notification/marksingle', [CustomersController::class, 'markRead'])->middleware(['auth:sanctum', 'type.customer']);

        Route::get('list/notification/markall', [CustomersController::class, 'markAllRead'])->middleware(['auth:sanctum', 'type.customer']);

        Route::get('downliners/next', [CustomersController::class, 'downlinerLevels'])->middleware(['auth:sanctum', 'type.customer']);

        Route::post('generateId', [CustomersController::class, 'generateId']);

        Route::post('register', [CustomersController::class, 'create']);
        Route::post('login', [CustomersController::class, 'login']);
        Route::post('forgotpassword', [CustomersController::class, 'forgotpassword']);
        Route::post('addpassword', [CustomersController::class, 'addpassword'])->middleware(['auth:sanctum', 'type.customer']);

        Route::post('change/password', [CustomersController::class, 'changePassword'])->middleware(['auth:sanctum', 'type.customer']);

        Route::get('profile', [CustomersController::class, 'getData'])->middleware(['auth:sanctum', 'type.customer']);
        Route::get('logout', [CustomersController::class, 'logout'])->middleware(['auth:sanctum', 'type.customer']);
        Route::get('logout', [CustomersController::class, 'logout'])->middleware(['auth:sanctum', 'type.customer']);

        Route::get('verifypayments', [CustomersController::class, 'verifyPayments'])->middleware(['auth:sanctum', 'type.customer']);
        Route::post('manualpayments', [PaymentsController::class, 'manualpayments'])->middleware(['auth:sanctum', 'type.customer']);

        Route::post('update', [CustomersController::class, 'updateProfile'])->middleware(['auth:sanctum', 'type.customer']);

        Route::post('upload/profile/img', [CustomersController::class, 'uploadProfile'])->middleware(['auth:sanctum', 'type.customer']);
        Route::prefix('kyc')->group(function () {
            Route::post('create', [KycController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('help_center')->group(function () {
            Route::post('create', [HelpCenterController::class, 'store'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('list', [HelpCenterController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('documents')->group(function () {
            Route::post('create', [DocumentsController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
        });

        Route::prefix('payments')->group(function () {
            Route::post('list', [PaymentsController::class, 'customerPaymentList'])->middleware(['auth:sanctum', 'type.customer']);

            Route::get('invoice', [PaymentsController::class, 'paymentInvoice']);

            Route::post('generatewallet', [CustomersController::class, 'generateWallet'])->middleware(['auth:sanctum', 'type.customer']);

        });

        Route::prefix('wallet')->group(function () {
            Route::post('fund', [TransactionsController::class, 'create'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('verify', [TransactionsController::class, 'verifyPayments'])->middleware(['auth:sanctum', 'type.customer']);
            Route::get('banks', [TransactionsController::class, 'listBanks'])->middleware(['auth:sanctum', 'type.customer']);
            Route::post('webhook', [CustomersController::class, 'paymentWebhook']);
            Route::get('transactions', [TransactionsController::class, 'index'])->middleware(['auth:sanctum', 'type.customer']);

            Route::prefix('transfer')->group(function () {
                Route::post('create', [TransactionsController::class, 'createTransfer'])->middleware(['auth:sanctum', 'type.customer']);
            });

        });

    });

    Route::prefix('plans')->group(function () {
        Route::post('create', [PlansController::class, 'create']);
        Route::post('edit', [PlansController::class, 'edit']);
        Route::post('delete', [PlansController::class, 'delete'])->middleware(['auth:sanctum', 'type.admin']);
        Route::get('list', [PlansController::class, 'index']);
    });

});
