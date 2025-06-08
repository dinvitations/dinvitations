<?php

use App\Livewire\ShowTemplates;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::middleware(Authenticate::class)->group(function () {
    Route::get('/template-views/{slug}', ShowTemplates::class)->name('templates.show');
});
