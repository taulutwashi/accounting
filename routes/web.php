<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('can:admin')->group(function () {
        Route::livewire('staff', 'staff.index')->name('staff.index');
        Route::livewire('staff/create', 'staff.create')->name('staff.create');
        Route::livewire('staff/{user}/edit', 'staff.edit')->name('staff.edit');

        Route::livewire('bank-withdrawals', 'bank-withdrawals.index')->name('bank-withdrawals.index');
        Route::livewire('bank-withdrawals/create', 'bank-withdrawals.create')->name('bank-withdrawals.create');

        Route::livewire('staff-allocations', 'staff-allocations.index')->name('staff-allocations.index');
        Route::livewire('staff-allocations/create', 'staff-allocations.create')->name('staff-allocations.create');
        Route::livewire('staff-allocations/{staff}', 'staff-allocations.show')->name('staff-allocations.show');
    });

    Route::livewire('my-expenses', 'my-expenses.index')->name('my-expenses.index');
    Route::livewire('my-expenses/create', 'my-expenses.create')->name('my-expenses.create');
});

require __DIR__.'/settings.php';
