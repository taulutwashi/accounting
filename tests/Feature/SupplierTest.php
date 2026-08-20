<?php

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

test('suppliers are paginated with ten items per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 11) as $supplierNumber) {
        Supplier::create(['name' => "Supplier {$supplierNumber}"]);
    }

    Volt::actingAs($admin)
        ->test('suppliers.⚡index')
        ->assertViewHas('suppliers', function (LengthAwarePaginator $suppliers): bool {
            return $suppliers->perPage() === 10
                && $suppliers->count() === 10
                && $suppliers->total() === 11;
        });
});

test('admin can search suppliers by their details', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Supplier::create(['name' => 'Acme Materials', 'email' => 'sales@acme.test']);
    Supplier::create(['name' => 'Gulf Trading', 'email' => 'hello@gulf.test']);

    Volt::actingAs($admin)
        ->test('suppliers.⚡index')
        ->set('search', 'acme')
        ->assertSet('paginators.page', 1)
        ->assertSee('Acme Materials')
        ->assertDontSee('Gulf Trading')
        ->assertViewHas('suppliers', function (LengthAwarePaginator $suppliers): bool {
            return $suppliers->total() === 1;
        });
});

test('supplier table renders compact icon actions and details', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Supplier::create([
        'name' => 'Acme Materials',
        'email' => 'sales@acme.test',
        'phone' => '555-0100',
        'cr_number' => 'CR-123',
        'vat_number' => 'VAT-456',
    ]);

    Volt::actingAs($admin)
        ->test('suppliers.⚡index')
        ->assertSee('Supplier Details')
        ->assertSeeInOrder(['Name', 'CR Number', 'VAT Number', 'Email', 'Phone', 'Actions'])
        ->assertSee('CR-123')
        ->assertSee('VAT-456')
        ->assertSeeHtml('aria-label="View Acme Materials"')
        ->assertSeeHtml('aria-label="Edit Acme Materials"')
        ->assertSeeHtml('aria-label="Delete Acme Materials"')
        ->assertSeeHtml('wire:confirm="Are you sure you want to delete this supplier?"');
});

test('supplier can be deleted from the table actions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $supplier = Supplier::create(['name' => 'Obsolete Supplier']);

    Volt::actingAs($admin)
        ->test('suppliers.⚡index')
        ->call('delete', $supplier)
        ->assertHasNoErrors();

    $this->assertSoftDeleted($supplier);
});
