<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/version', function () {
    $version = Storage::disk('local')->exists('version.txt')
        ? trim(Storage::disk('local')->get('version.txt'))
        : 'unknown';

    return response()->json(['version' => $version]);
});
