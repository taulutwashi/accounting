<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
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
        // Get all staff members with their allocations and expenses
        $staffMembers = User::whereIn('role', ['admin', 'staff'])
            ->when(
                trim($this->search) !== '',
                fn ($query) => $query->where(function ($q) {
                    $q->where('name', 'like', '%'.trim($this->search).'%')
                      ->orWhere('email', 'like', '%'.trim($this->search).'%');
                })
            )
            ->with(['allocations', 'expenses'])
            ->latest()
            ->paginate(10);

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

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex justify-end border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search staff balances…"
                aria-label="Search staff balances"
                size="sm"
                clearable
                class="w-full sm:w-72"
            />
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-xs">
                <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800/80 dark:text-zinc-300">
                    <tr>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Staff Member</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-right font-semibold dark:border-zinc-700">Total Allocated</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-right font-semibold dark:border-zinc-700">Total Spent</th>
                        <th scope="col" class="w-32 border-r border-b border-zinc-200 px-3 py-2 text-right font-semibold dark:border-zinc-700">Balance</th>
                        <th scope="col" class="w-32 border-b border-zinc-200 px-3 py-2 text-center font-semibold dark:border-zinc-700">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-700 dark:text-zinc-200">
                    @forelse ($staffMembers as $staff)
                        @php
                            $allocated = $staff->allocations->sum('amount');
                            $spent = $staff->expenses->sum('amount');
                            $balance = $staff->balance;
                        @endphp
                        <tr wire:key="staff-{{ $staff->id }}" class="transition-colors hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                            <td class="border-r border-zinc-200 px-3 py-1.5 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&color=7F9CF5&background=EBF4FF" size="sm" />
                                    <div class="grid flex-1 text-sm leading-tight">
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $staff->name }}</span>
                                        <span class="text-zinc-500">{{ $staff->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-right font-medium dark:border-zinc-700 dark:text-white">
                                ${{ number_format($allocated, 2) }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-right font-medium dark:border-zinc-700 dark:text-white">
                                ${{ number_format($spent, 2) }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-right font-medium dark:border-zinc-700">
                                <span class="{{ $balance < 0 ? 'text-red-500' : ($balance > 0 ? 'text-emerald-600' : 'text-zinc-900 dark:text-white') }}">
                                    ${{ number_format($balance, 2) }}
                                </span>
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-center">
                                    <flux:button href="{{ route('staff-allocations.show', $staff) }}" size="sm" variant="ghost" class="h-7 text-xs px-2 border border-zinc-200 dark:border-zinc-700">View Ledger</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No staff members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <flux:pagination
            :paginator="$staffMembers"
            class="border-zinc-200 px-3 py-2.5 dark:border-zinc-700"
        />
    </div>
</div>