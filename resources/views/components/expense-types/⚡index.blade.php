<?php

use Livewire\Component;
use App\Models\ExpenseType;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public function mount()
    {
        Gate::authorize('admin');
    }

    public function delete(ExpenseType $expenseType)
    {
        Gate::authorize('admin');
        $expenseType->delete();
    }

    public function with(): array
    {
        return [
            'expenseTypes' => ExpenseType::latest()->get(),
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

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Created</flux:table.column>
            <flux:table.column>Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($expenseTypes as $type)
                <flux:table.row>
                    <flux:table.cell>{{ $type->name }}</flux:table.cell>
                    <flux:table.cell>{{ $type->created_at->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('expense-types.edit', $type) }}" size="sm" variant="ghost">Edit</flux:button>
                        <flux:button wire:click="delete({{ $type->id }})" wire:confirm="Are you sure you want to delete this expense type?" size="sm" variant="ghost" class="text-red-600 hover:text-red-700">Delete</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>