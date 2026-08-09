<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PartController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);
    Route::get('/customers/{customer}/devices', [CustomerController::class, 'devices']);
    Route::post('/customers/{customer}/devices', [CustomerController::class, 'storeDevice']);
    Route::get('/customers/{customer}/work-orders', [CustomerController::class, 'workOrders']);

    Route::get('/work-orders', [WorkOrderController::class, 'index']);
    Route::post('/work-orders', [WorkOrderController::class, 'store']);
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show']);
    Route::put('/work-orders/{workOrder}', [WorkOrderController::class, 'update']);
    Route::post('/work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign']);
    Route::post('/work-orders/{workOrder}/status', [WorkOrderController::class, 'status']);
    Route::get('/work-orders/{workOrder}/history', [WorkOrderController::class, 'history']);
    Route::get('/work-orders/{workOrder}/notes', [WorkOrderController::class, 'notes']);
    Route::post('/work-orders/{workOrder}/notes', [WorkOrderController::class, 'addNote']);
    Route::post('/work-orders/{workOrder}/parts', [WorkOrderController::class, 'useParts']);

    Route::get('/technicians', [TechnicianController::class, 'index']);
    Route::post('/technicians', [TechnicianController::class, 'store']);
    Route::get('/technicians/{technician}', [TechnicianController::class, 'show']);
    Route::put('/technicians/{technician}', [TechnicianController::class, 'update']);
    Route::get('/technicians/{technician}/work-orders', [TechnicianController::class, 'workOrders']);
    Route::get('/technicians/{technician}/performance', [TechnicianController::class, 'performance']);

    Route::get('/parts', [PartController::class, 'index']);
    Route::post('/parts', [PartController::class, 'store']);
    Route::get('/parts/{part}', [PartController::class, 'show']);
    Route::put('/parts/{part}', [PartController::class, 'update']);
    Route::get('/parts/low-stock', [PartController::class, 'lowStock']);
    Route::post('/parts/{part}/restock', [PartController::class, 'restock']);
    Route::post('/parts/usage', [PartController::class, 'usage']);
    Route::get('/parts/suppliers', [PartController::class, 'suppliers']);
    Route::post('/parts/suppliers', [PartController::class, 'storeSupplier']);
    Route::get('/parts/purchase-orders', [PartController::class, 'purchaseOrders']);
    Route::post('/parts/purchase-orders', [PartController::class, 'storePurchaseOrder']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
    Route::post('/payments/{payment}/refund', [InvoiceController::class, 'refund']);

    Route::get('/reports/revenue', [ReportController::class, 'revenue']);
    Route::get('/reports/technicians', [ReportController::class, 'technicians']);
    Route::get('/reports/popular-repairs', [ReportController::class, 'popularRepairs']);
    Route::get('/reports/inventory', [ReportController::class, 'inventory']);
    Route::get('/reports/export/{type}', [ReportController::class, 'export']);
});
