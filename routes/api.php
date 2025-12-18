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
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Upload routes
        Route::post('upload/image', [UploadController::class, 'uploadImage']);
        Route::post('upload/images', [UploadController::class, 'uploadMultipleImages']);
        Route::post('upload/file', [UploadController::class, 'uploadFile']);
        Route::delete('upload/image', [UploadController::class, 'deleteImage']);

        // Settings
        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings', [SettingController::class, 'update']);

        // Users & Roles
        Route::get('users/roles', [UserController::class, 'roles']);
        Route::apiResource('users', UserController::class);

        // Role Management
        Route::get('permissions', [RoleController::class, 'permissions']);
        Route::put('roles/{id}/permissions', [RoleController::class, 'syncPermissions']);
        Route::apiResource('roles', RoleController::class);

        // Permission Management
        Route::get('permissions/groups', [PermissionController::class, 'groups']);
        Route::post('permissions/groups/rename', [PermissionController::class, 'renameGroup']);
        Route::post('permissions/groups/delete', [PermissionController::class, 'deleteGroup']);
        Route::apiResource('permissions', PermissionController::class);

        // Login Logs
        Route::get('login-logs/statistics', [LoginLogController::class, 'statistics']);
        Route::apiResource('login-logs', LoginLogController::class)->only(['index', 'show', 'destroy']);

        // Products & Categories
        Route::get('products/categories', [ProductController::class, 'categories']);
        Route::get('products/units', [ProductController::class, 'units']);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('categories', CategoryController::class);

        // Stock Management (read-only for reporting/alerts)
        Route::get('stock-ledger', [StockLedgerController::class, 'index']);
        Route::get('stock-ledger/{stock_ledger}', [StockLedgerController::class, 'show']);
        Route::get('stock-ledger/warehouses', [StockLedgerController::class, 'warehouses']);
        Route::apiResource('stocks', StockController::class)->only(['index', 'show']);

        // Purchases / Suppliers
        Route::apiResource('purchases', PurchaseController::class);
        Route::apiResource('suppliers', SupplierController::class);

        // Sales / Customers / Payments
        Route::apiResource('sales', SaleController::class);
        Route::apiResource('customers', CustomerController::class);
        Route::get('payments', [PaymentController::class, 'index']);
        Route::post('payments', [PaymentController::class, 'store']);

        // Units & Warehouses
        Route::apiResource('units', UnitController::class);
        Route::apiResource('warehouses', WarehouseController::class);

        // Reports
        Route::prefix('reports')->group(function () {
            // Sales Reports
            Route::get('sales', [ReportController::class, 'salesReport']);
            Route::get('sales/export/excel', [ReportController::class, 'exportSalesExcel']);
            Route::get('sales/export/pdf', [ReportController::class, 'exportSalesPDF']);
            
            // Purchase Reports
            Route::get('purchases', [ReportController::class, 'purchaseReport']);
            Route::get('purchases/export/excel', [ReportController::class, 'exportSalesExcel']);
            Route::get('purchases/export/pdf', [ReportController::class, 'exportSalesPDF']);
            
            // Stock Reports
            Route::get('stock', [ReportController::class, 'stockReport']);
            Route::get('stock/export/excel', [ReportController::class, 'exportSalesExcel']);
            Route::get('stock/export/pdf', [ReportController::class, 'exportSalesPDF']);
            
            // Due Reports
            Route::get('due', [ReportController::class, 'dueReport']);
            Route::get('due/export/excel', [ReportController::class, 'exportSalesExcel']);
            Route::get('due/export/pdf', [ReportController::class, 'exportSalesPDF']);
            
            // Profit Reports
            Route::get('profit', [ReportController::class, 'profitReport']);
            Route::get('profit/export/excel', [ReportController::class, 'exportSalesExcel']);
            Route::get('profit/export/pdf', [ReportController::class, 'exportSalesPDF']);
        });
    });
});
