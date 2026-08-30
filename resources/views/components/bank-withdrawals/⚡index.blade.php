<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BankWithdrawal;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount()
    {
        Gate::authorize('admin');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $search = trim($this->search);

        return [
            'withdrawals' => BankWithdrawal::query()
                ->with('user')
                ->when(
                    $search !== '',
                    fn ($query) => $query->where(
                        fn ($query) => $query
                            ->where('notes', 'like', '%'.$search.'%')
                            ->orWhere('amount', 'like', '%'.$search.'%')
                            ->orWhereHas('user', fn ($query) => $query->where('name', 'like', '%'.$search.'%')),
                    ),
                )
                ->latest('withdrawn_at')
                ->paginate(10),
            'totalWithdrawn' => BankWithdrawal::query()->sum('amount'),
        ];
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Bank Withdrawals</flux:heading>
            <flux:subheading size="lg" class="mb-6">Track money taken from the bank by company admins.</flux:subheading>
            <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Total Withdrawn: ${{ number_format($totalWithdrawn, 2) }}
            </div>
        </div>
        <flux:button href="{{ route('bank-withdrawals.create') }}" variant="primary">Record Withdrawal</flux:button>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex justify-end border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search withdrawals…"
                aria-label="Search bank withdrawals"
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
                        <th scope="col" class="w-44 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Date</th>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Admin</th>
                        <th scope="col" class="w-44 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Amount</th>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Notes</th>
                        <th scope="col" class="w-32 border-b border-zinc-200 px-3 py-2 text-center font-semibold dark:border-zinc-700">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-700 dark:text-zinc-200">
                    @forelse ($withdrawals as $withdrawal)
                        <tr wire:key="bank-withdrawal-{{ $withdrawal->id }}" class="transition-colors hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                            <td class="border-r border-zinc-200 px-2 py-1.5 text-center text-zinc-500 tabular-nums dark:border-zinc-700 dark:text-zinc-400">
                                {{ $withdrawals->firstItem() + $loop->index }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $withdrawal->withdrawn_at->format('M d, Y') }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($withdrawal->user->name) }}&color=7F9CF5&background=EBF4FF" size="sm" />
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $withdrawal->user->name }}</span>
                                </div>
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 font-medium tabular-nums text-zinc-900 dark:border-zinc-700 dark:text-white">
                                ${{ number_format($withdrawal->amount, 2) }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $withdrawal->notes ?: '-' }}
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <flux:modal.trigger name="bank-withdrawal-details-{{ $withdrawal->id }}">
                                        <flux:button icon="eye" variant="ghost" size="sm" aria-label="View withdrawal on {{ $withdrawal->withdrawn_at->format('M d, Y') }}" title="View details" class="size-7! border border-zinc-200 dark:border-zinc-700" />
                                    </flux:modal.trigger>
                                </div>

                                <flux:modal name="bank-withdrawal-details-{{ $withdrawal->id }}" class="max-w-md">
                                    <div class="space-y-4">
                                        <flux:heading size="lg">Withdrawal Details</flux:heading>

                                        <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                            <dt class="text-zinc-500 dark:text-zinc-400">Date</dt>
                                            <dd class="text-zinc-700 dark:text-zinc-200">{{ $withdrawal->withdrawn_at->format('M d, Y') }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Admin</dt>
                                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $withdrawal->user->name }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Amount</dt>
                                            <dd class="font-medium text-zinc-900 dark:text-white">${{ number_format($withdrawal->amount, 2) }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Notes</dt>
                                            <dd class="whitespace-normal text-zinc-700 dark:text-zinc-200">{{ $withdrawal->notes ?: '-' }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Recorded</dt>
                                            <dd class="text-zinc-700 dark:text-zinc-200">{{ $withdrawal->created_at->format('M d, Y g:i A') }}</dd>
                                        </dl>

                                        <div class="flex justify-end">
                                            <flux:modal.close>
                                                <flux:button variant="filled" size="sm">Close</flux:button>
                                            </flux:modal.close>
                                        </div>
                                    </div>
                                </flux:modal>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No bank withdrawals found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <flux:pagination :paginator="$withdrawals" class="border-zinc-200 px-3 py-2.5 dark:border-zinc-700" />
    </div>
</div>
