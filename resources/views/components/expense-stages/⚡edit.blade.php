<?php

use Livewire\Component;
use App\Models\ExpenseStage;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public ExpenseStage $expenseStage;
    public string $name = '';

    public function mount(ExpenseStage $expenseStage)
    {
        Gate::authorize('admin');
        
        $this->expenseStage = $expenseStage;
        $this->name = $expenseStage->name;
    }

    public function save()
    {
        Gate::authorize('admin');
        
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->expenseStage->update($validated);

        $this->redirect(route('expense-stages.index'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('expense-stages.index') }}" variant="ghost" icon="arrow-left" class="!px-2" />
        <div>
            <flux:heading size="xl" level="1">Edit Expense Stage</flux:heading>
            <flux:subheading size="lg">Update expense stage details.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <flux:input wire:model="name" label="Name" placeholder="e.g. Processing" />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button href="{{ route('expense-stages.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
</div>