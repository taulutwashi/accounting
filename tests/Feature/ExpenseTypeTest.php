<?php

use App\Models\User;
use App\Models\ExpenseType;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view expense types index', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/expense-types')
        ->assertOk()
        ->assertSee('Expense Types');
});

test('staff cannot view expense types index', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get('/expense-types')
        ->assertForbidden();
});

test('admin can create expense type', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/expense-types/create')
        ->assertOk();

    Volt::actingAs($admin)
        ->test('expense-types.⚡create')
        ->set('name', 'Travel')
        ->call('save')
        ->assertRedirect('/expense-types');

    $this->assertDatabaseHas('expense_types', [
        'name' => 'Travel',
    ]);
});
