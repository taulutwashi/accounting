<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

new class extends Component
{
    public User $user;

    public string $name = '';
    public ?string $designation = null;
    public string $email = '';
    public string $password = '';

    public function mount(User $user)
    {
        Gate::authorize('admin');
        
        $this->user = $user;
        $this->name = $user->name;
        $this->designation = $user->designation;
        $this->email = $user->email;
    }

    public function save()
    {
        Gate::authorize('admin');
        
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $this->user->name = $this->name;
        $this->user->designation = $this->designation;
        $this->user->email = $this->email;
        
        if (! empty($this->password)) {
            $this->user->password = Hash::make($this->password);
        }

        $this->user->save();

        $this->redirect(route('staff.index'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('staff.index') }}" variant="ghost" icon="arrow-left" class="!px-2" />
        <div>
            <flux:heading size="xl" level="1">Edit Staff</flux:heading>
            <flux:subheading size="lg">Update staff account details.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <flux:input wire:model="name" label="Name" placeholder="John Doe" />
        <flux:input wire:model="designation" label="Designation" placeholder="e.g. Senior Accountant" />
        <flux:input wire:model="email" type="email" label="Email address" placeholder="john@example.com" />
        <flux:input wire:model="password" type="password" label="Password (leave blank to keep current)" />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button href="{{ route('staff.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
</div>