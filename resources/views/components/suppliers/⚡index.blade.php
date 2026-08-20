<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount()
    {
       // Gate::authorize('admin');
    }

    public function delete(Supplier $supplier)
    {
       // Gate::authorize('admin');
        $supplier->delete();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $search = trim($this->search);

        return [
            'suppliers' => Supplier::query()
                ->when(
                    $search !== '',
                    fn ($query) => $query->where(
                        fn ($query) => $query
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('address', 'like', '%'.$search.'%')
                            ->orWhere('cr_number', 'like', '%'.$search.'%')
                            ->orWhere('vat_number', 'like', '%'.$search.'%'),
                    ),
                )
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Suppliers</flux:heading>
            <flux:subheading size="lg" class="mb-6">Manage your suppliers list.</flux:subheading>
        </div>
        <flux:button href="{{ route('suppliers.create') }}" variant="primary">Add Supplier</flux:button>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex justify-end border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search suppliers…"
                aria-label="Search suppliers"
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
                        <th scope="col" class="w-44 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">CR Number</th>
                        <th scope="col" class="w-44 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">VAT Number</th>
                        <th scope="col" class="w-64 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Email</th>
                        <th scope="col" class="w-48 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Phone</th>
                        <th scope="col" class="w-32 border-b border-zinc-200 px-3 py-2 text-center font-semibold dark:border-zinc-700">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-700 dark:text-zinc-200">
                    @forelse ($suppliers as $supplier)
                        <tr wire:key="supplier-{{ $supplier->id }}" class="transition-colors hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                            <td class="border-r border-zinc-200 px-2 py-1.5 text-center text-zinc-500 tabular-nums dark:border-zinc-700 dark:text-zinc-400">
                                {{ $suppliers->firstItem() + $loop->index }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 font-medium text-zinc-900 dark:border-zinc-700 dark:text-white">
                                {{ $supplier->name }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $supplier->cr_number ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $supplier->vat_number ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $supplier->email ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $supplier->phone ?? '-' }}
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <flux:modal.trigger name="supplier-details-{{ $supplier->id }}">
                                        <flux:button icon="eye" variant="ghost" size="sm" aria-label="View {{ $supplier->name }}" title="View details" class="size-7! border border-zinc-200 dark:border-zinc-700" />
                                    </flux:modal.trigger>

                                    <flux:button href="{{ route('suppliers.edit', $supplier) }}" icon="pencil" variant="ghost" size="sm" aria-label="Edit {{ $supplier->name }}" title="Edit" wire:navigate class="size-7! border border-zinc-200 dark:border-zinc-700" />

                                    <flux:button icon="trash" variant="ghost" size="sm" wire:click="delete({{ $supplier->id }})" wire:confirm="Are you sure you want to delete this supplier?" aria-label="Delete {{ $supplier->name }}" title="Delete" class="size-7! border border-red-200 text-red-600! hover:bg-red-50! hover:text-red-700! dark:border-red-900 dark:hover:bg-red-950/40!" />
                                </div>

                                <flux:modal name="supplier-details-{{ $supplier->id }}" class="max-w-md">
                                    <div class="space-y-4">
                                        <flux:heading size="lg">Supplier Details</flux:heading>

                                        <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                            <dt class="text-zinc-500 dark:text-zinc-400">Name</dt>
                                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $supplier->name }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Email</dt>
                                            <dd class="text-zinc-700 dark:text-zinc-200">{{ $supplier->email ?? '-' }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Phone</dt>
                                            <dd class="text-zinc-700 dark:text-zinc-200">{{ $supplier->phone ?? '-' }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">CR Number</dt>
                                            <dd class="text-zinc-700 dark:text-zinc-200">{{ $supplier->cr_number ?? '-' }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">VAT Number</dt>
                                            <dd class="text-zinc-700 dark:text-zinc-200">{{ $supplier->vat_number ?? '-' }}</dd>
                                            <dt class="text-zinc-500 dark:text-zinc-400">Address</dt>
                                            <dd class="whitespace-normal text-zinc-700 dark:text-zinc-200">{{ $supplier->address ?? '-' }}</dd>
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
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">No suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <flux:pagination :paginator="$suppliers" class="border-zinc-200 px-3 py-2.5 dark:border-zinc-700" />
    </div>
</div>
