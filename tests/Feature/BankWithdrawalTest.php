<?php

use App\Models\BankWithdrawal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

test('admin can view bank withdrawals index', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/bank-withdrawals')
        ->assertOk()
        ->assertSee('Bank Withdrawals');
});

test('staff cannot view bank withdrawals index', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get('/bank-withdrawals')
        ->assertForbidden();
});

test('bank withdrawals are paginated with ten items per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 11) as $withdrawalNumber) {
        BankWithdrawal::create([
            'user_id' => $admin->id,
            'amount' => $withdrawalNumber * 10,
            'withdrawn_at' => now()->subDays($withdrawalNumber),
        ]);
    }

    Volt::actingAs($admin)
        ->test('bank-withdrawals.⚡index')
        ->assertViewHas('withdrawals', function (LengthAwarePaginator $withdrawals): bool {
            return $withdrawals->perPage() === 10
                && $withdrawals->count() === 10
                && $withdrawals->total() === 11;
        });
});

test('admin can search bank withdrawals by admin name or notes', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Alice Admin']);
    $otherAdmin = User::factory()->create(['role' => 'admin', 'name' => 'Bob Admin']);

    BankWithdrawal::create([
        'user_id' => $admin->id,
        'amount' => 100,
        'withdrawn_at' => today(),
        'notes' => 'Office supplies',
    ]);

    BankWithdrawal::create([
        'user_id' => $otherAdmin->id,
        'amount' => 200,
        'withdrawn_at' => today(),
        'notes' => 'Travel expenses',
    ]);

    Volt::actingAs($admin)
        ->test('bank-withdrawals.⚡index')
        ->set('search', 'office')
        ->assertSet('paginators.page', 1)
        ->assertSee('Office supplies')
        ->assertDontSee('Travel expenses')
        ->assertViewHas('withdrawals', function (LengthAwarePaginator $withdrawals): bool {
            return $withdrawals->total() === 1;
        });
});

test('bank withdrawal table renders compact icon actions and details', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Alice Admin']);

    BankWithdrawal::create([
        'user_id' => $admin->id,
        'amount' => 150.50,
        'withdrawn_at' => today(),
        'notes' => 'Petty cash refill',
    ]);

    Volt::actingAs($admin)
        ->test('bank-withdrawals.⚡index')
        ->assertSee('Withdrawal Details')
        ->assertSeeInOrder(['#', 'Date', 'Admin', 'Amount', 'Notes', 'Actions'])
        ->assertSee('Alice Admin')
        ->assertSee('$150.50')
        ->assertSee('Petty cash refill')
        ->assertSeeHtml('aria-label="View withdrawal on '.today()->format('M d, Y').'"');
});

test('bank withdrawals index shows total withdrawn amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    BankWithdrawal::create([
        'user_id' => $admin->id,
        'amount' => 100,
        'withdrawn_at' => today(),
    ]);

    BankWithdrawal::create([
        'user_id' => $admin->id,
        'amount' => 250.75,
        'withdrawn_at' => today()->subDay(),
    ]);

    Volt::actingAs($admin)
        ->test('bank-withdrawals.⚡index')
        ->assertSee('Total Withdrawn: $350.75');
});
