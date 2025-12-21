<?php

use App\Http\Controllers\Api\auth\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\logs\LoginLogController;
use App\Http\Controllers\Api\products\CategoryController;
use App\Http\Controllers\Api\products\ProductController;
use App\Http\Controllers\Api\settings\SettingController;
use App\Http\Controllers\Api\stock\StockController;
use App\Http\Controllers\Api\stock\StockLedgerController;
use App\Http\Controllers\Api\upload\UploadController;
use App\Http\Controllers\Api\users\PermissionController;
use App\Http\Controllers\Api\users\RoleController;
use App\Http\Controllers\Api\users\UserController;
use App\Http\Controllers\Api\master\SupplierController;
use App\Http\Controllers\Api\master\CustomerController;
use App\Http\Controllers\Api\purchase\PurchaseController;
use App\Http\Controllers\Api\payments\PaymentController;
use App\Http\Controllers\Api\sales\SaleController;
use App\Http\Controllers\Api\products\UnitController;
use App\Http\Controllers\Api\stock\WarehouseController;
use App\Http\Controllers\Api\reports\ReportController;
use App\Http\Controllers\Api\reports\SalesReportController;
use App\Http\Controllers\Api\reports\PurchaseReportController;
use App\Http\Controllers\Api\reports\StockReportController;
use App\Http\Controllers\Api\reports\DueReportController;
use App\Http\Controllers\Api\reports\ProfitReportController;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
    return response()->json(['message' => 'Hello World']);
});

// Test Telegram Notification
Route::get('test/telegram', function () {
    try {
        $message = request()->get('message', null);
        $chatId = request()->get('chat_id', null);
        
        $result = \App\Services\TelegramNotify::T_NOTIFY($message, $chatId);
        
        if ($result === false) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram notification failed. Check logs for details.',
                'config' => [
                    'bot_token_configured' => !empty(config('telegram.bots.mybot.token')) || !empty(env('TELEGRAM_BOT_TOKEN')),
                    'chat_id_configured' => !empty(config('values.telegram_chat_id')) || !empty(env('TELEGRAM_CHAT_ID')),
                ]
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Telegram notification sent successfully!',
            'response' => [
                'message_id' => $result->messageId ?? $result->get('message_id'),
                'chat_id' => $result->chat->id ?? $result->get('chat')->get('id'),
                'date' => $result->date ?? $result->get('date'),
                'text' => $result->text ?? $result->get('text'),
            ]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
});



Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/public/settings', [SettingController::class, 'publicIndex']);

    // Protected admin routes - requires authentication and admin access
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('permission:access-dashboard');

        // Upload routes
        Route::post('upload/image', [UploadController::class, 'uploadImage'])->middleware('permission:upload-files');
        Route::post('upload/images', [UploadController::class, 'uploadMultipleImages'])->middleware('permission:upload-files');
        Route::post('upload/file', [UploadController::class, 'uploadFile'])->middleware('permission:upload-files');
        Route::delete('upload/image', [UploadController::class, 'deleteImage'])->middleware('permission:upload-files');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->middleware('permission:manage-settings');
        Route::post('settings', [SettingController::class, 'update'])->middleware('permission:manage-settings');

        // Users & Roles
        Route::get('users/roles', [UserController::class, 'roles'])->middleware('permission:manage-users');
        Route::apiResource('users', UserController::class)->middleware('permission:manage-users');

        // Role Management
        Route::get('permissions', [RoleController::class, 'permissions'])->middleware('permission:manage-roles');
        Route::put('roles/{id}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:manage-roles');
        Route::apiResource('roles', RoleController::class)->middleware('permission:manage-roles');

        // Permission Management
        Route::get('permissions/groups', [PermissionController::class, 'groups'])->middleware('permission:manage-roles');
        Route::post('permissions/groups/rename', [PermissionController::class, 'renameGroup'])->middleware('permission:manage-roles');
        Route::post('permissions/groups/delete', [PermissionController::class, 'deleteGroup'])->middleware('permission:manage-roles');
        Route::apiResource('permissions', PermissionController::class)->middleware('permission:manage-roles');

        // Login Logs
        Route::get('login-logs/statistics', [LoginLogController::class, 'statistics'])->middleware('permission:view-login-logs');
        Route::apiResource('login-logs', LoginLogController::class)->only(['index', 'show', 'destroy'])->middleware('permission:view-login-logs');

        // Products & Categories
        Route::get('products/categories', [ProductController::class, 'categories'])->middleware('permission:view-products');
        Route::get('products/units', [ProductController::class, 'units'])->middleware('permission:view-products');
        Route::get('products/warehouses', [ProductController::class, 'warehouses'])->middleware('permission:view-products');
        Route::apiResource('products', ProductController::class)->only(['index', 'show'])->middleware('permission:view-products');
        Route::apiResource('products', ProductController::class)->only(['store'])->middleware('permission:create-products');
        Route::apiResource('products', ProductController::class)->only(['update'])->middleware('permission:edit-products');
        Route::apiResource('products', ProductController::class)->only(['destroy'])->middleware('permission:delete-products');
        Route::apiResource('categories', CategoryController::class)->middleware('permission:manage-categories');

        // Stock Management (read-only for reporting/alerts)
        Route::get('stock-ledger', [StockLedgerController::class, 'index'])->middleware('permission:view-stock-ledger');
        Route::get('stock-ledger/warehouses', [StockLedgerController::class, 'warehouses'])->middleware('permission:view-stock-ledger');
        Route::get('stock-ledger/{stock_ledger}', [StockLedgerController::class, 'show'])->middleware('permission:view-stock-ledger');
        Route::apiResource('stocks', StockController::class)->only(['index', 'show'])->middleware('permission:view-stock-levels,manage-warehouses');
        Route::apiResource('stocks', StockController::class)->only(['store'])->middleware('permission:manage-warehouses');

        // Purchases / Suppliers
        Route::apiResource('purchases', PurchaseController::class)->only(['index', 'show'])->middleware('permission:view-purchases');
        Route::apiResource('purchases', PurchaseController::class)->only(['store', 'update', 'destroy'])->middleware('permission:create-purchase');
        Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->middleware('permission:create-purchase');
        Route::apiResource('suppliers', SupplierController::class)->middleware('permission:manage-suppliers');

        // Sales / Customers / Payments
        Route::apiResource('sales', SaleController::class)->only(['index', 'show'])->middleware('permission:view-sales');
        Route::apiResource('sales', SaleController::class)->only(['store', 'update', 'destroy'])->middleware('permission:create-sale');
        Route::apiResource('customers', CustomerController::class)->middleware('permission:manage-customers');
        Route::get('payments', [PaymentController::class, 'index'])->middleware('permission:view-payments');
        Route::post('payments', [PaymentController::class, 'store'])->middleware('permission:record-payment');

        // Units & Warehouses
        Route::apiResource('units', UnitController::class)->middleware('permission:manage-units');
        Route::apiResource('warehouses', WarehouseController::class)->only(['index', 'show'])->middleware('permission:view-warehouses,manage-warehouses');
        Route::apiResource('warehouses', WarehouseController::class)->only(['store', 'update', 'destroy'])->middleware('permission:manage-warehouses');

        // Reports
        Route::prefix('reports')->middleware('permission:view-reports')->group(function () {
            // Sales Reports
            Route::get('sales', [SalesReportController::class, 'index']);
            Route::get('sales/export/excel', [SalesReportController::class, 'exportExcel'])->middleware('permission:export-reports');
            Route::get('sales/export/pdf', [SalesReportController::class, 'exportPDF'])->middleware('permission:export-reports');
            
            // Purchase Reports
            Route::get('purchases', [PurchaseReportController::class, 'index']);
            Route::get('purchases/export/excel', [PurchaseReportController::class, 'exportExcel'])->middleware('permission:export-reports');
            Route::get('purchases/export/pdf', [PurchaseReportController::class, 'exportPDF'])->middleware('permission:export-reports');
            
            // Stock Reports
            Route::get('stock', [StockReportController::class, 'index']);
            Route::get('stock/export/excel', [StockReportController::class, 'exportExcel'])->middleware('permission:export-reports');
            Route::get('stock/export/pdf', [StockReportController::class, 'exportPDF'])->middleware('permission:export-reports');
            
            // Due Reports
            Route::get('due', [DueReportController::class, 'index']);
            Route::get('due/export/excel', [DueReportController::class, 'exportExcel'])->middleware('permission:export-reports');
            Route::get('due/export/pdf', [DueReportController::class, 'exportPDF'])->middleware('permission:export-reports');
            
            // Profit Reports
            Route::get('profit', [ProfitReportController::class, 'index']);
            Route::get('profit/export/excel', [ProfitReportController::class, 'exportExcel'])->middleware('permission:export-reports');
            Route::get('profit/export/pdf', [ProfitReportController::class, 'exportPDF'])->middleware('permission:export-reports');
        });
    });
});
