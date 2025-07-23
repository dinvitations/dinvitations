<?php

use App\Http\Controllers\Api\QRCodeController;
use App\Http\Controllers\GrapesJSUploadController;
use App\Http\Middleware\VerifyQRApiKey;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::middleware(VerifyQRApiKey::class)->group(function () {
    Route::post('/scan-qrcode', [QRCodeController::class, 'store'])->name('api.scan_qrcode');
});

Route::get('/qr/view', [QRCodeController::class, 'view'])
    ->middleware('signed')
    ->name('api.qr_view');

Route::post('/grapesjs/upload', [GrapesJSUploadController::class, 'upload'])->name('grapesjs.upload');

Route::get('/version', function () {
    $version = Storage::disk('local')->exists('version.txt')
        ? trim(Storage::disk('local')->get('version.txt'))
        : 'unknown';

    return response()->json(['version' => $version]);
});
