<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public User $staff;

    public function mount(User $staff)
    {
        Gate::authorize('admin');
        if ($staff->role !== 'staff') {
            abort(404);
        }
        $this->staff = $staff;
    }

    public function with(): array
    {
        // Combine allocations and expenses into a single timeline/ledger
        $allocations = $this->staff->allocations()->get()->map(function ($item) {
            return [
                'type' => 'allocation',
                'date' => $item->allocated_at,
                'amount' => $item->amount,
                'description' => 'Funds Allocated by Admin',
                'notes' => $item->notes,
                'created_at' => $item->created_at,
            ];
        });

        $expenses = $this->staff->expenses()->get()->map(function ($item) {
            return [
                'type' => 'expense',
                'date' => $item->spent_at,
                'amount' => -$item->amount,
                'description' => $item->purpose,
                'notes' => $item->notes,
                'created_at' => $item->created_at,
            ];
        });

        $ledger = $allocations->concat($expenses)
            ->sortByDesc(function ($item) {
                return $item['date']->format('Y-m-d') . '-' . $item['created_at']->timestamp;
            })
            ->values();

        return [
            'ledger' => $ledger,
            'allocated' => $this->staff->allocations()->sum('amount'),
            'spent' => $this->staff->expenses()->sum('amount'),
            'balance' => $this->staff->balance,
        ];
    }
};
?>

<div>
    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&color=7F9CF5&background=EBF4FF" size="md" />
                <flux:heading size="xl" level="1">{{ $staff->name }}'s Ledger</flux:heading>
            </div>
            <flux:subheading size="lg">Detailed history of funds allocated and business expenses.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('staff-allocations.create', ['staff_id' => $staff->id]) }}" variant="primary">Allocate Funds</flux:button>
            <flux:button href="{{ route('staff-allocations.index') }}" variant="ghost">Back to List</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <flux:card>
            <div class="text-sm font-medium text-zinc-500 mb-1">Total Allocated</div>
            <div class="text-2xl font-semibold text-emerald-600">${{ number_format($allocated, 2) }}</div>
        </flux:card>
        
        <flux:card>
            <div class="text-sm font-medium text-zinc-500 mb-1">Total Spent</div>
            <div class="text-2xl font-semibold text-red-500">${{ number_format($spent, 2) }}</div>
        </flux:card>

        <flux:card>
            <div class="text-sm font-medium text-zinc-500 mb-1">Current Balance</div>
            <div class="text-2xl font-semibold {{ $balance < 0 ? 'text-red-500' : 'text-zinc-900 dark:text-white' }}">${{ number_format($balance, 2) }}</div>
        </flux:card>
    </div>

    <flux:heading size="lg" class="mb-4">Transaction History</flux:heading>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Description</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($ledger as $entry)
                <flux:table.row>
                    <flux:table.cell>{{ $entry['date']->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if($entry['type'] === 'allocation')
                            <flux:badge color="green" size="sm">Allocation</flux:badge>
                        @else
                            <flux:badge color="red" size="sm">Expense</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="font-medium text-zinc-900 dark:text-white">{{ $entry['description'] }}</div>
                        @if($entry['notes'])
                            <div class="text-xs text-zinc-500 mt-1">{{ $entry['notes'] }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-medium {{ $entry['amount'] > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $entry['amount'] > 0 ? '+' : '' }}${{ number_format(abs($entry['amount']), 2) }}
                        </span>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center text-zinc-500 py-6">No transactions found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>