<?php

use App\Livewire\ShowInvitation;
use App\Livewire\ShowTemplate;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::middleware(Authenticate::class)->group(function () {
    Route::get('/template-views/{slug}', ShowTemplate::class)->name('templates.show');
    Route::get('/{slug}', ShowInvitation::class)->name('invitation.show');
});
