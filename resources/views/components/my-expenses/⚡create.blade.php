<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|string|max:255')]
    public $purpose = '';

    #[Validate('required|date')]
    public $spent_at = '';

    #[Validate('nullable|string')]
    public $notes = '';

    public function mount()
    {
        $this->spent_at = today()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        Expense::create([
            'staff_id' => Auth::id(),
            'amount' => $this->amount,
            'purpose' => $this->purpose,
            'spent_at' => $this->spent_at,
            'notes' => $this->notes,
        ]);

        return redirect()->route('my-expenses.index');
    }
};
?>

<div>
    <div class="mb-6">
        <flux:heading size="xl" level="1">Log Business Expense</flux:heading>
        <flux:subheading size="lg">Record a new expense against your allocated funds.</flux:subheading>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="space-y-6">
            <flux:input 
                wire:model="purpose" 
                label="Purpose of Expense" 
                placeholder="e.g., Office Supplies, Client Lunch" 
                required 
            />

            <flux:input 
                wire:model="amount" 
                label="Amount Spent" 
                type="number" 
                step="0.01" 
                min="0.01"
                placeholder="0.00"
                icon="currency-dollar" 
                required 
            />

            <flux:input 
                wire:model="spent_at" 
                label="Date of Expense" 
                type="date" 
                required 
            />

            <flux:textarea 
                wire:model="notes" 
                label="Additional Notes (Optional)" 
                placeholder="Receipt numbers, vendor names, or detailed descriptions."
                rows="4" 
            />

            <div class="flex gap-4">
                <flux:button type="submit" variant="primary">Log Expense</flux:button>
                <flux:button href="{{ route('my-expenses.index') }}" variant="ghost">Cancel</flux:button>
            </div>
        </div>
    </form>
</div>