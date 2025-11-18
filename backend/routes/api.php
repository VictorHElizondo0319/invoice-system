<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', [HealthController::class, 'check']);

// Invoice endpoints
Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('jwt.auth');
Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->middleware('jwt.auth');

// Protected invoice actions (require authentication)
Route::middleware(['jwt.auth'])->group(function () {
	// Admin-only invoice management
	Route::post('/invoices', [InvoiceController::class, 'store'])->middleware('role:admin');
	Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->middleware('role:admin');
	Route::put('/invoices/{id}/submit', [InvoiceController::class, 'submit'])->middleware('role:admin');
	Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->middleware('role:admin');

	// Export/upload PDF: allowed for invoice owner or admins — controller enforces ownership
	Route::post('/invoice/{id}/export', [InvoiceController::class, 'export']);

	// User endpoints (used as customers) - search & create (create is admin-only)
	Route::get('/users', [UserController::class, 'index']);
	Route::post('/users', [UserController::class, 'store'])->middleware('role:admin');
});

// Auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware(['jwt.auth'])->group(function () {
	Route::post('/auth/logout', [AuthController::class, 'logout']);
	Route::get('/auth/me', [AuthController::class, 'me']);
});

// Payment endpoints
Route::post('/payments', [PaymentController::class, 'store']);
Route::get('/payments', [PaymentController::class, 'index']);

// Report endpoints
Route::get('/reports/summary', [ReportController::class, 'summary']);
Route::get('/reports/analytics', [ReportController::class, 'analytics']);


