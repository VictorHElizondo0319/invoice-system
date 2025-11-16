<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Invoice & Payment System API',
        'version' => '1.0.0',
        'endpoints' => [
            'health' => '/api/health',
            'invoices' => '/api/invoices',
            'payments' => '/api/payments',
            'reports' => '/api/reports/summary'
        ]
    ]);
});


