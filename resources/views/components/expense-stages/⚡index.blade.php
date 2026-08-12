<?php

use Livewire\Component;
use App\Models\ExpenseStage;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public function mount()
    {
        Gate::authorize('admin');
    }

    public function delete(ExpenseStage $expenseStage)
    {
        Gate::authorize('admin');
        $expenseStage->delete();
    }

    public function with(): array
    {
        return [
            'expenseStages' => ExpenseStage::latest()->get(),
        ];
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Expense Stages</flux:heading>
            <flux:subheading size="lg" class="mb-6">Manage your expense stages.</flux:subheading>
        </div>
        <flux:button href="{{ route('expense-stages.create') }}" variant="primary">Add Expense Stage</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Created</flux:table.column>
            <flux:table.column>Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($expenseStages as $stage)
                <flux:table.row>
                    <flux:table.cell>{{ $stage->name }}</flux:table.cell>
                    <flux:table.cell>{{ $stage->created_at->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('expense-stages.edit', $stage) }}" size="sm" variant="ghost">Edit</flux:button>
                        <flux:button wire:click="delete({{ $stage->id }})" wire:confirm="Are you sure you want to delete this expense stage?" size="sm" variant="ghost" class="text-red-600 hover:text-red-700">Delete</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>