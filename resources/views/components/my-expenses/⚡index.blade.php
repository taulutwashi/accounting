<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function with(): array
    {
        $user = Auth::user();

        // Get allocations
        $allocations = $user->allocations()->get()->map(function ($item) {
            return [
                'type' => 'allocation',
                'date' => $item->allocated_at,
                'amount' => $item->amount,
                'description' => 'Funds Received from Admin',
                'material' => null,
                'receipt_no' => null,
                'created_at' => $item->created_at,
            ];
        });

        // Get expenses
        $expenses = $user->expenses()->with(['supplier', 'type', 'stage'])->get()->map(function ($item) {
            return [
                'type' => 'expense',
                'date' => $item->spent_at,
                'amount' => -$item->amount,
                'description' => $item->description,
                'material' => $item->material,
                'receipt_no' => $item->receipt_no,
                'created_at' => $item->created_at,
            ];
        });

        // Combine and sort
        $ledger = $allocations->concat($expenses)
            ->sortByDesc(function ($item) {
                return $item['date']->format('Y-m-d') . '-' . $item['created_at']->timestamp;
            })
            ->values();

        return [
            'ledger' => $ledger,
            'allocated' => $user->allocations()->sum('amount'),
            'spent' => $user->expenses()->sum('amount'),
            'balance' => $user->balance,
        ];
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">My Expenses</flux:heading>
            <flux:subheading size="lg">Track your business expenses and remaining funds.</flux:subheading>
        </div>
        <flux:button href="{{ route('my-expenses.create') }}" variant="primary">Log Expense</flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <flux:card>
            <div class="text-sm font-medium text-zinc-500 mb-1">Total Funds Received</div>
            <div class="text-2xl font-semibold text-emerald-600">${{ number_format($allocated, 2) }}</div>
        </flux:card>
        
        <flux:card>
            <div class="text-sm font-medium text-zinc-500 mb-1">Total Spent</div>
            <div class="text-2xl font-semibold text-red-500">${{ number_format($spent, 2) }}</div>
        </flux:card>

        <flux:card>
            <div class="text-sm font-medium text-zinc-500 mb-1">Available Balance</div>
            <div class="text-2xl font-semibold {{ $balance < 0 ? 'text-red-500' : 'text-zinc-900 dark:text-white' }}">${{ number_format($balance, 2) }}</div>
        </flux:card>
    </div>

    <flux:heading size="lg" class="mb-4">Transaction History</flux:heading>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Details</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($ledger as $entry)
                <flux:table.row>
                    <flux:table.cell>{{ $entry['date']->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if($entry['type'] === 'allocation')
                            <flux:badge color="green" size="sm">Funds Received</flux:badge>
                        @else
                            <flux:badge color="red" size="sm">Expense</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($entry['type'] === 'allocation')
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $entry['description'] }}</div>
                        @else
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $entry['material'] }}</div>
                            <div class="text-xs text-zinc-500 mt-1">
                                Receipt: {{ $entry['receipt_no'] }}
                                @if($entry['description'])
                                    | {{ str()->limit($entry['description'], 50) }}
                                @endif
                            </div>
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