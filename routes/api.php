<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductStockController;
use App\Http\Controllers\Api\StockRequestController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:super_admin'])->get('/test-super-admin-only', function () {
    return response()->json(['message' => 'Kamu berhasil akses sebagai Super Admin!']);
});

Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('users', UserController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::middleware('role:super_admin,warehouse_admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    Route::middleware('role:super_admin,warehouse_admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/branches/{branch}/stocks', [ProductStockController::class, 'byBranch']);
    Route::get('/products/{product}/stocks', [ProductStockController::class, 'byProduct']);

    Route::get('/stock-requests', [StockRequestController::class, 'index']);
    Route::get('/stock-requests/{stockRequest}', [StockRequestController::class, 'show']);

    Route::middleware('role:staff')->group(function () {
        Route::post('/stock-requests', [StockRequestController::class, 'store']);
    });

    Route::post('/stock-requests/{stockRequest}/approve', [StockRequestController::class, 'approve']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stock-trend', [DashboardController::class, 'stockTrend']);

    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::get('/stock-movements/export/excel', [StockMovementController::class, 'exportExcel']);
    Route::get('/stock-movements/export/pdf', [StockMovementController::class, 'exportPdf']);
    Route::get('/product-stocks/export/excel', [ProductStockController::class, 'exportExcel']);
    Route::get('/product-stocks/export/pdf', [ProductStockController::class, 'exportPdf']);

    Route::get('/product-stocks', [ProductStockController::class, 'index']);
    Route::post('/product-stocks', [ProductStockController::class, 'store']);

});


