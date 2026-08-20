<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount()
    {
        Gate::authorize('admin');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'staff' => User::where('role', 'staff')
                ->when(
                    trim($this->search) !== '',
                    fn ($query) => $query->where(function ($q) {
                        $q->where('name', 'like', '%'.trim($this->search).'%')
                          ->orWhere('email', 'like', '%'.trim($this->search).'%')
                          ->orWhere('designation', 'like', '%'.trim($this->search).'%');
                    })
                )
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Staff Management</flux:heading>
            <flux:subheading size="lg" class="mb-6">Manage your company staff members.</flux:subheading>
        </div>
        <flux:button href="{{ route('staff.create') }}" variant="primary">Add Staff</flux:button>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex justify-end border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search staff members…"
                aria-label="Search staff members"
                size="sm"
                clearable
                class="w-full sm:w-72"
            />
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-xs">
                <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800/80 dark:text-zinc-300">
                    <tr>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Name</th>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Designation</th>
                        <th scope="col" class="border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Email</th>
                        <th scope="col" class="w-44 border-r border-b border-zinc-200 px-3 py-2 text-left font-semibold dark:border-zinc-700">Joined</th>
                        <th scope="col" class="w-32 border-b border-zinc-200 px-3 py-2 text-center font-semibold dark:border-zinc-700">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 text-zinc-700 dark:divide-zinc-700 dark:text-zinc-200">
                    @forelse ($staff as $user)
                        <tr wire:key="staff-{{ $user->id }}" class="transition-colors hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                            <td class="border-r border-zinc-200 px-3 py-1.5 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" size="sm" />
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 dark:border-zinc-700 dark:text-zinc-300">
                                {{ $user->designation ?? '-' }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 dark:border-zinc-700 dark:text-zinc-300">
                                {{ $user->email }}
                            </td>
                            <td class="border-r border-zinc-200 px-3 py-1.5 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-center">
                                    <flux:button href="{{ route('staff.edit', $user) }}" icon="pencil" variant="ghost" size="sm" aria-label="Edit {{ $user->name }}" title="Edit" wire:navigate class="size-7! border border-zinc-200 dark:border-zinc-700" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No staff members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <flux:pagination
            :paginator="$staff"
            class="border-zinc-200 px-3 py-2.5 dark:border-zinc-700"
        />
    </div>
</div>