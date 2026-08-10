<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\BankWithdrawal;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|date')]
    public $withdrawn_at = '';

    #[Validate('nullable|string')]
    public $notes = '';

    public function mount()
    {
        Gate::authorize('admin');
        $this->withdrawn_at = today()->format('Y-m-d');
    }

    public function save()
    {
        Gate::authorize('admin');

        $this->validate();

        BankWithdrawal::create([
            'user_id' => auth()->id(),
            'amount' => $this->amount,
            'withdrawn_at' => $this->withdrawn_at,
            'notes' => $this->notes,
        ]);

        return redirect()->route('bank-withdrawals.index');
    }
};
?>

<div>
    <div class="mb-6">
        <flux:heading size="xl" level="1">Record Bank Withdrawal</flux:heading>
        <flux:subheading size="lg">Log a new withdrawal from the company bank account.</flux:subheading>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="space-y-6">
            <flux:input 
                wire:model="amount" 
                label="Amount" 
                type="number" 
                step="0.01" 
                min="0.01"
                placeholder="0.00"
                icon="currency-dollar" 
                required 
            />

            <flux:input 
                wire:model="withdrawn_at" 
                label="Date" 
                type="date" 
                required 
            />

            <flux:textarea 
                wire:model="notes" 
                label="Notes (Optional)" 
                placeholder="Reason for withdrawal, references, etc."
                rows="4" 
            />

            <div class="flex gap-4">
                <flux:button type="submit" variant="primary">Save Withdrawal</flux:button>
                <flux:button href="{{ route('bank-withdrawals.index') }}" variant="ghost">Cancel</flux:button>
            </div>
        </div>
    </form>
</div>