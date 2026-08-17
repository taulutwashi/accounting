<?php

use Livewire\Component;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public Supplier $supplier;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    public function mount(Supplier $supplier)
    {
        Gate::authorize('admin');
        
        $this->supplier = $supplier;
        $this->name = $supplier->name;
        $this->email = $supplier->email ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';
    }

    public function save()
    {
        Gate::authorize('admin');
        
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $this->supplier->update($validated);

        $this->redirect(route('suppliers.index'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('suppliers.index') }}" variant="ghost" icon="arrow-left" class="!px-2" />
        <div>
            <flux:heading size="xl" level="1">Edit Supplier</flux:heading>
            <flux:subheading size="lg">Update supplier details.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <flux:input wire:model="name" label="Name" placeholder="e.g. Acme Corp" required />
        <flux:input wire:model="email" type="email" label="Email" placeholder="contact@acme.com" />
        <flux:input wire:model="phone" label="Phone" placeholder="+1 234 567 8900" />
        <flux:textarea wire:model="address" label="Address" placeholder="123 Business St..." />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button href="{{ route('suppliers.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
</div>
