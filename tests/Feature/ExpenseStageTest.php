<?php

use App\Models\ExpenseStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Volt;

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

test('expense stages are paginated with ten items per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 11) as $stageNumber) {
        ExpenseStage::create(['name' => "Stage {$stageNumber}"]);
    }

    Volt::actingAs($admin)
        ->test('expense-stages.⚡index')
        ->assertViewHas('expenseStages', function (LengthAwarePaginator $expenseStages): bool {
            return $expenseStages->perPage() === 10
                && $expenseStages->count() === 10
                && $expenseStages->total() === 11;
        });
});

test('admin can search expense stages by name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ExpenseStage::create(['name' => 'Foundation']);
    ExpenseStage::create(['name' => 'Painting']);

    Volt::actingAs($admin)
        ->test('expense-stages.⚡index')
        ->set('search', 'paint')
        ->assertSet('paginators.page', 1)
        ->assertSee('Painting')
        ->assertDontSee('Foundation')
        ->assertViewHas('expenseStages', function (LengthAwarePaginator $expenseStages): bool {
            return $expenseStages->total() === 1;
        });
});

test('admin can edit an expense stage', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $expenseStage = ExpenseStage::create(['name' => 'Pending']);

    Volt::actingAs($admin)
        ->test('expense-stages.⚡edit', ['expenseStage' => $expenseStage])
        ->set('name', 'Approved')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('expense-stages.index'));

    expect($expenseStage->fresh()->name)->toBe('Approved');
});

test('admin can delete an expense stage from the table actions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $expenseStage = ExpenseStage::create(['name' => 'Obsolete']);

    Volt::actingAs($admin)
        ->test('expense-stages.⚡index')
        ->assertSee('Expense Stage Details')
        ->assertSeeHtml('aria-label="View Obsolete"')
        ->assertSeeHtml('aria-label="Edit Obsolete"')
        ->assertSeeHtml('aria-label="Delete Obsolete"')
        ->assertSeeHtml('wire:confirm="Are you sure you want to delete this expense stage?"')
        ->call('delete', $expenseStage)
        ->assertHasNoErrors();

    $this->assertSoftDeleted($expenseStage);
});
