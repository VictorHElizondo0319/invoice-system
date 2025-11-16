<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HealthController;

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
Route::get('/invoices', [InvoiceController::class, 'index']);
Route::post('/invoices', [InvoiceController::class, 'store']);
Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
Route::put('/invoices/{id}/submit', [InvoiceController::class, 'submit']);

// Export invoice as PDF (supports client request at `/invoice/{id}/export`)
Route::post('/invoice/{id}/export', [InvoiceController::class, 'export']);

// Payment endpoints
Route::post('/payments', [PaymentController::class, 'store']);
Route::get('/payments', [PaymentController::class, 'index']);

// Report endpoints
Route::get('/reports/summary', [ReportController::class, 'summary']);
Route::get('/reports/analytics', [ReportController::class, 'analytics']);


