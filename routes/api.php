<?php

use App\Http\Controllers\Api\QRCodeController;
use App\Http\Middleware\VerifyQRApiKey;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyQRApiKey::class)->group(function () {
    Route::post('/scan-qrcode', [QRCodeController::class, 'store'])->name('api.scan_qrcode');
});

Route::get('/qr-pdf', [QRCodeController::class, 'view'])
    ->middleware('signed')
    ->name('api.qr_pdf');
