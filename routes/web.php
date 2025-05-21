<?php

use App\Http\Controllers\TemplateBuilderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.web.auth.login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::post('/templates/save', [TemplateBuilderController::class, 'save'])->name('templates.save');
});

require __DIR__.'/auth.php';
