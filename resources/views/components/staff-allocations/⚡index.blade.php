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
        // Get all staff members with their allocations and expenses
        $staffMembers = User::where('role', 'staff')
            ->with(['allocations', 'expenses'])
            ->get();

        return [
            'staffMembers' => $staffMembers,
        ];
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Staff Balances</flux:heading>
            <flux:subheading size="lg">Overview of funds allocated to staff and their expenses.</flux:subheading>
        </div>
        <flux:button href="{{ route('staff-allocations.create') }}" variant="primary">Allocate Funds</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Staff Member</flux:table.column>
            <flux:table.column>Total Allocated</flux:table.column>
            <flux:table.column>Total Spent</flux:table.column>
            <flux:table.column>Balance</flux:table.column>
            <flux:table.column>Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($staffMembers as $staff)
                @php
                    $allocated = $staff->allocations->sum('amount');
                    $spent = $staff->expenses->sum('amount');
                    $balance = $staff->balance;
                @endphp
                <flux:table.row>
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&color=7F9CF5&background=EBF4FF" size="sm" />
                            <div class="grid flex-1 text-sm leading-tight">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $staff->name }}</span>
                                <span class="text-zinc-500">{{ $staff->email }}</span>
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>${{ number_format($allocated, 2) }}</flux:table.cell>
                    <flux:table.cell>${{ number_format($spent, 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <span class="font-medium {{ $balance < 0 ? 'text-red-500' : ($balance > 0 ? 'text-emerald-600' : '') }}">
                            ${{ number_format($balance, 2) }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('staff-allocations.show', $staff) }}" size="sm" variant="ghost">View Ledger</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>