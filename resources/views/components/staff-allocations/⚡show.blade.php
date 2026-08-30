<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithPagination;

    public User $staff;
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount(User $staff)
    {
        Gate::authorize('admin');
        if (!in_array($staff->role, ['admin', 'staff'])) {
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
            ->filter(function ($item) {
                if (trim($this->search) === '') {
                    return true;
                }
                
                $search = strtolower(trim($this->search));
                return str_contains(strtolower($item['description']), $search) ||
                       str_contains(strtolower($item['notes'] ?? ''), $search) ||
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
            $ledger->slice(($page - 1) * $perPage, $perPage)->values(),
            $ledger->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return [
            'ledger' => $paginatedLedger,
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
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Date</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Type</th>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Description</th>
                        <th scope="col" class="w-32 border-b border-zinc-200 px-3 py-2 text-right font-semibold dark:border-zinc-700">Amount</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-700 dark:text-zinc-200">
                    @forelse ($ledger as $entry)
                        <tr class="transition-colors hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                            <td class="border-r border-zinc-200 px-2 py-1.5 text-center text-zinc-500 tabular-nums dark:border-zinc-700 dark:text-zinc-400">
                                {{ $loop->iteration }}
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
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $entry['description'] }}</div>
                                @if($entry['notes'])
                                    <div class="text-xs text-zinc-500 mt-1">{{ $entry['notes'] }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-1.5 text-right text-zinc-500 dark:text-zinc-400">
                                <span class="font-medium {{ $entry['amount'] > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $entry['amount'] > 0 ? '+' : '' }}${{ number_format(abs($entry['amount']), 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
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