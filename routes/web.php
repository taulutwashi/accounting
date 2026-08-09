<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('can:admin')->group(function () {
        Route::livewire('staff', 'staff.index')->name('staff.index');
        Route::livewire('staff/create', 'staff.create')->name('staff.create');
        Route::livewire('staff/{user}/edit', 'staff.edit')->name('staff.edit');
    });
});

require __DIR__.'/settings.php';
