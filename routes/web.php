<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ventes (gérant + vendeur)
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::patch('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

    // Clients & crédits (gérant + vendeur)
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/repayment', [CustomerController::class, 'repayment'])->name('customers.repayment');
    Route::post('/customers/{customer}/payment', [CustomerController::class, 'payment'])->name('customers.payment');

    // Profil utilisateur (tout le monde)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');

    // Gérant uniquement
    Route::middleware('role:gerant')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);

        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index','create','store','show']);
        Route::patch('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::patch('/purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/csv', [ReportController::class, 'exportCsv'])->name('reports.csv');

        // Gestion utilisateurs (gérant uniquement)
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    });
});
