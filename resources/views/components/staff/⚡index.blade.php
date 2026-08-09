<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public function mount()
    {
        Gate::authorize('admin');
    }

    public function with(): array
    {
        return [
            'staff' => User::where('role', 'staff')->get(),
        ];
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Staff Management</flux:heading>
            <flux:subheading size="lg" class="mb-6">Manage your company staff members.</flux:subheading>
        </div>
        <flux:button href="{{ route('staff.create') }}" variant="primary">Add Staff</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Joined</flux:table.column>
            <flux:table.column>Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($staff as $user)
                <flux:table.row>
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" size="sm" />
                            {{ $user->name }}
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>{{ $user->created_at->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('staff.edit', $user) }}" size="sm" variant="ghost">Edit</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>