<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithPagination;

    public function mount()
    {
        Gate::authorize('admin');
    }

    public function delete(Supplier $supplier)
    {
        Gate::authorize('admin');
        $supplier->delete();
    }

    public function with(): array
    {
        return [
            'suppliers' => Supplier::latest()->paginate(10),
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

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Phone</flux:table.column>
            <flux:table.column>Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($suppliers as $supplier)
                <flux:table.row>
                    <flux:table.cell>{{ $supplier->name }}</flux:table.cell>
                    <flux:table.cell>{{ $supplier->email ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $supplier->phone ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('suppliers.edit', $supplier) }}" size="sm" variant="ghost">Edit</flux:button>
                        <flux:button wire:click="delete({{ $supplier->id }})" wire:confirm="Are you sure you want to delete this supplier?" size="sm" variant="ghost" class="text-red-600 hover:text-red-700">Delete</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <div class="mt-6">
        {{ $suppliers->links() }}
    </div>
</div>
