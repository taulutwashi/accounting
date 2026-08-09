<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    public function mount()
    {
        Gate::authorize('admin');
    }

    public function save()
    {
        Gate::authorize('admin');
        
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'staff',
        ]);

        $this->redirect(route('staff.index'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('staff.index') }}" variant="ghost" icon="arrow-left" class="!px-2" />
        <div>
            <flux:heading size="xl" level="1">Add Staff</flux:heading>
            <flux:subheading size="lg">Create a new staff account.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <flux:input wire:model="name" label="Name" placeholder="John Doe" />
        <flux:input wire:model="email" type="email" label="Email address" placeholder="john@example.com" />
        <flux:input wire:model="password" type="password" label="Password" />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button href="{{ route('staff.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Create Staff Member</flux:button>
        </div>
    </form>
</div>