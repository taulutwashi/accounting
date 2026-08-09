<?php

use App\Models\User;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view staff index', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/staff')
        ->assertOk()
        ->assertSee('Staff Management');
});

test('staff cannot view staff index', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get('/staff')
        ->assertForbidden();
});

test('admin can create staff', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get('/staff/create')
        ->assertOk();

    Volt::actingAs($admin)
        ->test('staff.⚡create')
        ->set('name', 'New Staff')
        ->set('email', 'staff@example.com')
        ->set('password', 'password123')
        ->call('save')
        ->assertRedirect('/staff');

    $this->assertDatabaseHas('users', [
        'email' => 'staff@example.com',
        'role' => 'staff',
    ]);
});
