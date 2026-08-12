<?php

use Livewire\Component;
use App\Models\ExpenseType;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public ExpenseType $expenseType;
    public string $name = '';

    public function mount(ExpenseType $expenseType)
    {
        Gate::authorize('admin');
        
        $this->expenseType = $expenseType;
        $this->name = $expenseType->name;
    }

    public function save()
    {
        Gate::authorize('admin');
        
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->expenseType->update($validated);

        $this->redirect(route('expense-types.index'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('expense-types.index') }}" variant="ghost" icon="arrow-left" class="!px-2" />
        <div>
            <flux:heading size="xl" level="1">Edit Expense Type</flux:heading>
            <flux:subheading size="lg">Update expense type details.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <flux:input wire:model="name" label="Name" placeholder="e.g. Travel" />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button href="{{ route('expense-types.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
</div>