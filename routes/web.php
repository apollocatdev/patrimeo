<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.home');
});
Route::get('/health', fn() => response('OK', 200));
