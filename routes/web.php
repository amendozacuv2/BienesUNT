<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('home', 'dashboard')
    ->middleware(['auth'])
    ->name('home');

Route::redirect('dashboard', 'home');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
