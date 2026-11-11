<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\CustomerDeviceController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExportController;
use App\Http\Controllers\Web\InvoiceController;
use App\Http\Controllers\Web\PartController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\TechnicianController;
use App\Http\Controllers\Web\WarrantyController;
use App\Http\Controllers\Web\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/work-orders/devices-json', [WorkOrderController::class, 'devicesJson'])
        ->name('work-orders.devices-json');

    Route::post('/work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])
        ->name('work-orders.assign');
    Route::post('/work-orders/{workOrder}/status', [WorkOrderController::class, 'changeStatus'])
        ->name('work-orders.status');
    Route::post('/work-orders/{workOrder}/notes', [WorkOrderController::class, 'addNote'])
        ->name('work-orders.notes');
    Route::post('/work-orders/{workOrder}/parts', [WorkOrderController::class, 'useParts'])
        ->name('work-orders.parts');

    Route::resource('work-orders', WorkOrderController::class)->except('destroy');

    Route::resource('customers', CustomerController::class);
    Route::resource('customers.devices', CustomerDeviceController::class)->except('index', 'show');

    Route::resource('technicians', TechnicianController::class);

    Route::resource('parts', PartController::class);
    Route::post('/parts/{part}/restock', [PartController::class, 'restock'])->name('parts.restock');
    Route::post('/parts/{part}/adjust', [PartController::class, 'adjust'])->name('parts.adjust');

    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class)->except('edit', 'update');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/work-orders/{workOrder}/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/work-orders/{workOrder}/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    Route::post('/payments/{payment}/refund', [InvoiceController::class, 'refund'])->name('payments.refund');

    Route::get('/warranties', [WarrantyController::class, 'index'])->name('warranties.index');
    Route::get('/warranties/{warranty}', [WarrantyController::class, 'show'])->name('warranties.show');
    Route::post('/work-orders/{workOrder}/warranties', [WarrantyController::class, 'store'])->name('warranties.store');
    Route::post('/warranties/{warranty}/revoke', [WarrantyController::class, 'revoke'])->name('warranties.revoke');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/device-types', [SettingController::class, 'storeDeviceType'])->name('settings.device-types.store');
    Route::post('/settings/brands', [SettingController::class, 'storeBrand'])->name('settings.brands.store');
    Route::post('/settings/categories', [SettingController::class, 'storeCategory'])->name('settings.categories.store');
    Route::post('/settings/repair-services', [SettingController::class, 'storeRepairService'])->name('settings.repair-services.store');

    Route::get('/exports/invoices/{invoice}/pdf', [ExportController::class, 'invoicePdf'])->name('exports.invoice-pdf');
    Route::get('/exports/report', [ExportController::class, 'report'])->name('exports.report');
});
