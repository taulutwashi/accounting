<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\User;
use App\Models\StaffAllocation;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    #[Validate('required|exists:users,id')]
    public $staff_id = '';

    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|date')]
    public $allocated_at = '';

    #[Validate('nullable|string')]
    public $notes = '';

    public function mount()
    {
        Gate::authorize('admin');
        $this->allocated_at = today()->format('Y-m-d');
        
        if (request()->has('staff_id')) {
            $this->staff_id = request()->query('staff_id');
        }
    }

    public function with(): array
    {
        return [
            'staffMembers' => User::whereIn('role', ['admin', 'staff'])->get(),
        ];
    }

    public function save()
    {
        Gate::authorize('admin');
        $this->validate();

        // Ensure the selected user is actually staff
        $staff = User::findOrFail($this->staff_id);
        if (!in_array($staff->role, ['admin', 'staff'])) {
            $this->addError('staff_id', 'Selected user must be an admin or staff member.');
            return;
        }

        StaffAllocation::create([
            'admin_id' => auth()->id(),
            'staff_id' => $this->staff_id,
            'amount' => $this->amount,
            'allocated_at' => $this->allocated_at,
            'notes' => $this->notes,
        ]);

        return redirect()->route('staff-allocations.show', $this->staff_id);
    }
};
?>

<div>
    <div class="mb-6">
        <flux:heading size="xl" level="1">Allocate Funds to Staff</flux:heading>
        <flux:subheading size="lg">Record funds given to a staff member for business expenses.</flux:subheading>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="space-y-6">
            <flux:select wire:model="staff_id" label="Staff Member" placeholder="Choose staff member..." required>
                @foreach ($staffMembers as $staff)
                    <flux:select.option value="{{ $staff->id }}">{{ $staff->name }}</flux:select.option>
                @endforeach
            </flux:select>

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
                wire:model="allocated_at" 
                label="Date" 
                type="date" 
                required 
            />

            <flux:textarea 
                wire:model="notes" 
                label="Notes (Optional)" 
                placeholder="Reason for allocation, reference, etc."
                rows="4" 
            />

            <div class="flex gap-4">
                <flux:button type="submit" variant="primary">Allocate Funds</flux:button>
                <flux:button href="{{ route('staff-allocations.index') }}" variant="ghost">Cancel</flux:button>
            </div>
        </div>
    </form>
</div>