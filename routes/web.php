<?php

use App\Http\Controllers\Api\GrapesJSUploadController;
use App\Http\Controllers\Api\GreetingController;
use App\Http\Controllers\Api\QRCodeController;
use App\Http\Controllers\Api\RSVPController;
use App\Http\Controllers\Api\SelfieController;
use App\Livewire\GreetingDisplay;
use App\Livewire\SelfieDisplay;
use App\Livewire\SelfieStation;
use App\Livewire\ShowInvitation;
use App\Livewire\ShowTemplate;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::middleware(Authenticate::class)->group(function () {
    Route::post('/grapesjs/upload', [GrapesJSUploadController::class, 'upload'])->name('grapesjs.upload');
    Route::post('/selfie/upload', [SelfieController::class, 'upload'])->name('selfie.upload');
    Route::post('/qrcode/scan', [QRCodeController::class, 'store'])->name('qrcode.scan');

    Route::get('/selfie/capture/{guestId?}', SelfieStation::class)->name('selfie.capture');
    Route::get('/selfie/display/{guestId?}', SelfieDisplay::class)->name('selfie.display');

    Route::get('/greeting/display/{guestId?}', GreetingDisplay::class)->name('greeting.display');
    Route::post('/greeting/upload', [GreetingController::class, 'upload'])->name('greeting.upload');
});

Route::middleware('signed')->get('/qrcode/view', [QRCodeController::class, 'view'])->name('qrcode.view');
Route::middleware('signed')->get('/qrcode/print', [QRCodeController::class, 'print'])->name('qrcode.print');

Route::get('/template-views/{slug}', ShowTemplate::class)->name('templates.show');
Route::get('/{slug}', ShowInvitation::class)->name('invitation.show');

Route::post('/rsvp', [RSVPController::class, 'store'])->name('rsvp');
