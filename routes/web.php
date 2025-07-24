<?php

use App\Http\Controllers\Api\GrapesJSUploadController;
use App\Http\Controllers\Api\RSVPController;
use App\Http\Controllers\Api\SelfieController;
use App\Livewire\SelfieStation;
use App\Livewire\ShowInvitation;
use App\Livewire\ShowTemplate;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
Route::middleware(Authenticate::class)->group(function () {
    Route::post('/selfie/upload', [SelfieController::class, 'upload'])->name('selfie.upload');
    Route::get('/selfie/capture/{guestId?}', SelfieStation::class)->name('selfie.capture');
});


Route::get('/template-views/{slug}', ShowTemplate::class)->name('templates.show');
Route::get('/{slug}', ShowInvitation::class)->name('invitation.show');

Route::post('/grapesjs/upload', [GrapesJSUploadController::class, 'upload'])->name('grapesjs.upload');
Route::post('/rsvp', [RSVPController::class, 'store'])->name('rsvp');