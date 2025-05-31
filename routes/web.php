<?php

use App\Livewire\ShowTemplates;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::middleware(Authenticate::class)->group(function () {
    Route::get('/{slug}', ShowTemplates::class)->name('templates.show');
});

require __DIR__.'/auth.php';
