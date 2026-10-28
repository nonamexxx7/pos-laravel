<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::get('/', DashboardController::class)->name('dashboard');

    // การขาย + ชำระเงิน
    Route::get('/pos', [SaleController::class, 'create'])->name('pos');
    Route::post('/pos', [SaleController::class, 'store'])->name('pos.store');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    // สมาชิก
    Route::resource('members', MemberController::class)->except('show');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');

    // สินค้า (ดูได้ทุกคน)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/stock-movements', [ProductController::class, 'movements'])->name('stock.movements');

    Route::middleware('admin')->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::post('/products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('employees', EmployeeController::class)->except('show');
        Route::post('/sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});
