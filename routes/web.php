<?php

use App\Http\Controllers\TemplateBuilderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/templates/save', [TemplateBuilderController::class, 'save'])->name('templates.save');

require __DIR__.'/auth.php';
