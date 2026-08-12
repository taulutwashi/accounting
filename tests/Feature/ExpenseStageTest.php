<?php

use App\Models\User;
use App\Models\ExpenseStage;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view expense stages index', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/expense-stages')
        ->assertOk()
        ->assertSee('Expense Stages');
});

test('staff cannot view expense stages index', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get('/expense-stages')
        ->assertForbidden();
});

test('admin can create expense stage', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/expense-stages/create')
        ->assertOk();

    Volt::actingAs($admin)
        ->test('expense-stages.⚡create')
        ->set('name', 'Processing')
        ->call('save')
        ->assertRedirect('/expense-stages');

    $this->assertDatabaseHas('expense_stages', [
        'name' => 'Processing',
    ]);
});
