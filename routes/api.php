<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PenjualanApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Penjualan sync endpoints
Route::middleware('auth:sanctum')->prefix('penjualan')->group(function () {
    Route::post('/receive', [PenjualanApiController::class, 'receive']);
    Route::get('/online', [PenjualanApiController::class, 'sendOnline']);
});

// Health check endpoint (no auth required)
Route::get('/ping', [PenjualanApiController::class, 'ping']);
