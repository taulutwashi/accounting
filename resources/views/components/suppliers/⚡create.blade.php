<?php

use Livewire\Component;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $cr_number = '';
    public string $vat_number = '';

    public function mount()
    {
        Gate::authorize('admin');
    }

    public function save()
    {
        Gate::authorize('admin');
        
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'cr_number' => ['nullable', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
        ]);

        Supplier::create($validated);

        $this->redirect(route('suppliers.index'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('suppliers.index') }}" variant="ghost" icon="arrow-left" class="!px-2" />
        <div>
            <flux:heading size="xl" level="1">Add Supplier</flux:heading>
            <flux:subheading size="lg">Create a new supplier.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <flux:input wire:model="name" label="Name" placeholder="e.g. Acme Corp" required />
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input wire:model="cr_number" label="CR Number" placeholder="Commercial Registration Number" />
            <flux:input wire:model="vat_number" label="VAT Number" placeholder="Value Added Tax Number" />
            <flux:input wire:model="email" type="email" label="Email" placeholder="contact@acme.com" />
            <flux:input wire:model="phone" label="Phone" placeholder="+1 234 567 8900" />
        </div>
        
        <flux:textarea wire:model="address" label="Address" placeholder="123 Business St..." />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button href="{{ route('suppliers.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save</flux:button>
        </div>
    </form>
</div>
