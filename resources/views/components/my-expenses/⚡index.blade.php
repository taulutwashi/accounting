<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

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
                'supplier_name' => null,
                'expense_type' => null,
                'expense_stage' => null,
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
                'supplier_name' => $item->supplier?->name,
                'expense_type' => $item->type?->name,
                'expense_stage' => $item->stage?->name,
                'created_at' => $item->created_at,
            ];
        });

        // Combine and sort
        $ledger = $allocations->concat($expenses)
            ->filter(function ($item) {
                if (trim($this->search) === '') {
                    return true;
                }
                
                $search = strtolower(trim($this->search));
                return str_contains(strtolower($item['description'] ?? ''), $search) ||
                       str_contains(strtolower($item['material'] ?? ''), $search) ||
                       str_contains(strtolower($item['receipt_no'] ?? ''), $search) ||
                       str_contains(strtolower($item['supplier_name'] ?? ''), $search) ||
                       str_contains(strtolower($item['expense_type'] ?? ''), $search) ||
                       str_contains(strtolower($item['expense_stage'] ?? ''), $search) ||
                       str_contains(strtolower($item['type']), $search) ||
                       str_contains((string)abs($item['amount']), $search);
            })
            ->sortByDesc(function ($item) {
                return $item['date']->format('Y-m-d') . '-' . $item['created_at']->timestamp;
            })
            ->values();

        $page = $this->getPage();
        $perPage = 10;
        $paginatedLedger = new LengthAwarePaginator(
            $ledger->forPage($page, $perPage),
            $ledger->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'ledger' => $paginatedLedger,
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

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex justify-end border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search history…"
                aria-label="Search history"
                size="sm"
                clearable
                class="w-full sm:w-72"
            />
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-xs">
                <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800/80 dark:text-zinc-300">
                    <tr>
                        <th scope="col" class="w-10 border-r border-b border-zinc-200 px-2 py-2 text-center font-semibold dark:border-zinc-700">#</th>
                        <th scope="col" class="w-24 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Date of Expense</th>
                        <th scope="col" class="w-24 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Type</th>
                        <th scope="col" class="w-28 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Receipt No.</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Material</th>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Description</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Supplier</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Expense Type</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Expense Stage</th>
                        <th scope="col" class="w-28 border-b border-zinc-200 px-3 py-2 text-right font-semibold dark:border-zinc-700">Amount Spent</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-700 dark:text-zinc-200">
                    @forelse ($ledger as $entry)
                        <tr class="transition-colors hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                            <td class="border-r border-zinc-200 px-2 py-1.5 text-center text-zinc-500 tabular-nums dark:border-zinc-700 dark:text-zinc-400">
                                {{ $ledger->firstItem() + $loop->index }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 font-medium text-zinc-900 dark:border-zinc-700 dark:text-white">
                                {{ $entry['date']->format('M d, Y') }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                @if($entry['type'] === 'allocation')
                                    <flux:badge color="green" size="sm">Allocation</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">Expense</flux:badge>
                                @endif
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $entry['receipt_no'] ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $entry['material'] ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $entry['description'] ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $entry['supplier_name'] ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $entry['expense_type'] ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $entry['expense_stage'] ?? '-' }}
                            </td>
                            <td class="px-3 py-1.5 text-right text-zinc-500 dark:text-zinc-400">
                                <span class="font-medium {{ $entry['amount'] > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $entry['amount'] > 0 ? '+' : '' }}${{ number_format(abs($entry['amount']), 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <flux:pagination :paginator="$ledger" class="border-zinc-200 px-3 py-2.5 dark:border-zinc-700" />
    </div>
</div>