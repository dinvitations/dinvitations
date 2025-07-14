<?php

use App\Livewire\ShowInvitation;
use App\Livewire\ShowTemplate;
use Illuminate\Support\Facades\Route;

Route::get('/template-views/{slug}', ShowTemplate::class)->name('templates.show');
Route::get('/{slug}', ShowInvitation::class)->name('invitation.show');


