<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ExpenseType;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount()
    {
        Gate::authorize('admin');
    }

    public function delete(ExpenseType $expenseType)
    {
        Gate::authorize('admin');
        $expenseType->delete();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'expenseTypes' => ExpenseType::query()
                ->when(
                    trim($this->search) !== '',
                    fn ($query) => $query->where('name', 'like', '%'.trim($this->search).'%'),
                )
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Expense Types</flux:heading>
            <flux:subheading size="lg" class="mb-6">Manage your expense types.</flux:subheading>
        </div>
        <flux:button href="{{ route('expense-types.create') }}" variant="primary">Add Expense Type</flux:button>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex justify-end border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search expense types…"
                aria-label="Search expense types"
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
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Name</th>
                        <th scope="col" class="w-44 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Created</th>
                        <th scope="col" class="w-32 border-b border-zinc-200 px-3 py-2 text-center font-semibold dark:border-zinc-700">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-700 dark:text-zinc-200">
                    @forelse ($expenseTypes as $type)
                        <tr wire:key="expense-type-{{ $type->id }}" class="transition-colors hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                            <td class="border-r border-zinc-200 px-2 py-1.5 text-center text-zinc-500 tabular-nums dark:border-zinc-700 dark:text-zinc-400">
                                {{ $expenseTypes->firstItem() + $loop->index }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 font-medium text-zinc-900 dark:border-zinc-700 dark:text-white">
                                {{ $type->name }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $type->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <flux:modal.trigger name="expense-type-details-{{ $type->id }}">
                                        <flux:button icon="eye" variant="ghost" size="sm" aria-label="View {{ $type->name }}" title="View details" class="size-7! border border-zinc-200 dark:border-zinc-700" />
                                    </flux:modal.trigger>

                                    <flux:button href="{{ route('expense-types.edit', $type) }}" icon="pencil" variant="ghost" size="sm" aria-label="Edit {{ $type->name }}" title="Edit" wire:navigate class="size-7! border border-zinc-200 dark:border-zinc-700" />

                                    <flux:button icon="trash" variant="ghost" size="sm" wire:click="delete({{ $type->id }})" wire:confirm="Are you sure you want to delete this expense type?" aria-label="Delete {{ $type->name }}" title="Delete" class="size-7! border border-red-200 text-red-600! hover:bg-red-50! hover:text-red-700! dark:border-red-900 dark:hover:bg-red-950/40!" />
                                </div>

                                <flux:modal name="expense-type-details-{{ $type->id }}" class="max-w-sm">
                                    <div class="space-y-4">
                                        <flux:heading size="lg">Expense Type Details</flux:heading>

                                        <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                            <dt class="text-zinc-500 dark:text-zinc-400">Name</dt>
                                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $type->name }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Created</dt>
                                            <dd class="text-zinc-700 dark:text-zinc-200">{{ $type->created_at->format('M d, Y') }}</dd>
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
                            <td colspan="4" class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">No expense types found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <flux:pagination :paginator="$expenseTypes" class="border-zinc-200 px-3 py-2.5 dark:border-zinc-700" />
    </div>
</div>
