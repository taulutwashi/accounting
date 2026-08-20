<?php

use App\Models\ExpenseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Volt;

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

test('expense types are paginated with ten items per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 11) as $typeNumber) {
        ExpenseType::create(['name' => "Type {$typeNumber}"]);
    }

    Volt::actingAs($admin)
        ->test('expense-types.⚡index')
        ->assertViewHas('expenseTypes', function (LengthAwarePaginator $expenseTypes): bool {
            return $expenseTypes->perPage() === 10
                && $expenseTypes->count() === 10
                && $expenseTypes->total() === 11;
        });
});

test('admin can search expense types by name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ExpenseType::create(['name' => 'Materials']);
    ExpenseType::create(['name' => 'Travel']);

    Volt::actingAs($admin)
        ->test('expense-types.⚡index')
        ->set('search', 'trav')
        ->assertSet('paginators.page', 1)
        ->assertSee('Travel')
        ->assertDontSee('Materials')
        ->assertViewHas('expenseTypes', function (LengthAwarePaginator $expenseTypes): bool {
            return $expenseTypes->total() === 1;
        });
});

test('expense type table renders compact icon actions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ExpenseType::create(['name' => 'Travel']);

    Volt::actingAs($admin)
        ->test('expense-types.⚡index')
        ->assertSee('Expense Type Details')
        ->assertSeeHtml('aria-label="View Travel"')
        ->assertSeeHtml('aria-label="Edit Travel"')
        ->assertSeeHtml('aria-label="Delete Travel"')
        ->assertSeeHtml('wire:confirm="Are you sure you want to delete this expense type?"');
});

test('admin can delete an expense type from the table actions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $expenseType = ExpenseType::create(['name' => 'Obsolete']);

    Volt::actingAs($admin)
        ->test('expense-types.⚡index')
        ->call('delete', $expenseType)
        ->assertHasNoErrors();

    $this->assertSoftDeleted($expenseType);
});
