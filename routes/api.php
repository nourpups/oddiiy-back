<?php

use App\Enum\Locale;
use App\Http\Controllers;
use App\Http\Controllers\Admin;
use App\Http\Middleware\IsAdmin;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('{locale}')->group(static function () {
    Route::get('/', Controllers\HomeController::class);
    Route::get('/search', Controllers\SearchController::class);
    Route::middleware('auth:sanctum')->group(static function () {
        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        });
        Route::resource('/orders', Controllers\OrderController::class)->only(['index', 'store']);
    });

    Route::prefix('/auth')->group(static function () {
        Route::get('/otp-code', [Controllers\SmsVerficationController::class, 'sendOtp']);
        Route::get('/register-otp-code', [Controllers\SmsVerficationController::class, 'sendRegisterOtp']);
        Route::get('/login-otp-code', [Controllers\SmsVerficationController::class, 'sendLoginOtp']);
        Route::post('/verify-sms', [Controllers\SmsVerficationController::class, 'verifyOtp']);
        Route::controller(Controllers\AuthController::class)->group(static function () {
            Route::post('/login', 'login');
            Route::post('/register', 'register');
            Route::post('/reset-password', 'resetPassword');
            Route::post('/logout', 'logout')->middleware('auth:sanctum');
        });
    });

    Route::prefix('/products')
        ->controller(Controllers\ProductController::class)
        ->group(static function () {
            Route::get('/recommended', 'recommended');
            Route::get('/{product:slug}', 'show');
        });

    Route::get('/catalog', Controllers\CatalogController::class);

    Route::apiResource('users', Controllers\UserController::class)->only(['update']);
    Route::apiResource('categories', Controllers\CategoryController::class)->only(['index']);

    Route::prefix('/coupons')
        ->controller(Controllers\CouponController::class)
        ->group(static function () {
            Route::get('/first-order', 'firstOrder');
            Route::post('/apply', 'apply');
        });
    Route::apiResource('/admin/fonts', Admin\FontController::class)->only(['index', 'update']);

    Route::prefix('/admin')->as('admin.')
        ->middleware(['auth:sanctum', IsAdmin::class])
        ->group(static function () {
            Route::apiResource('products', Admin\ProductController::class)->scoped([
                'product' => 'slug'
            ]);
            Route::apiResource('categories', Admin\CategoryController::class)->scoped([
                'category' => 'slug'
            ]);
            Route::apiResource('collections', Admin\CollectionController::class);
            Route::apiResource('tags', Controllers\TagController::class);
            Route::apiResource('attributes', Admin\AttributeController::class)->except(['destroy']);
            Route::apiResource('attributes.attribute-options', Admin\AttributeOptionController::class)
                ->except(['index'])
                ->shallow();
            Route::apiResource('coupons', Admin\CouponController::class);
            Route::apiResource('cashback-options', Admin\CashbackWalletOptionsController::class)
                ->only(['index'])
                ->withoutMiddleware(IsAdmin::class);
        });
})->whereIn('locale', Locale::cases());

Route::any('/handle/{paysys}', function ($paysys) {
    (new Goodoneuz\PayUz\PayUz)->driver($paysys)->handle();
});
