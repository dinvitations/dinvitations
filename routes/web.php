<?php

use App\Http\Controllers\Api\GrapesJSUploadController;
use App\Http\Controllers\Api\RSVPController;
use App\Livewire\ShowInvitation;
use App\Livewire\ShowTemplate;
use Illuminate\Support\Facades\Route;

Route::get('/template-views/{slug}', ShowTemplate::class)->name('templates.show');
Route::get('/{slug}', ShowInvitation::class)->name('invitation.show');

Route::post('/grapesjs/upload', [GrapesJSUploadController::class, 'upload'])->name('grapesjs.upload');
Route::post('/rsvp', [RSVPController::class, 'store'])->name('rsvp');