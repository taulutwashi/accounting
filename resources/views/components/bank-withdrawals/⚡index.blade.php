<?php

use Livewire\Component;
use App\Models\BankWithdrawal;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public function mount()
    {
        Gate::authorize('admin');
    }

    public function with(): array
    {
        $withdrawals = BankWithdrawal::with('user')->latest('withdrawn_at')->get();
        $totalWithdrawn = $withdrawals->sum('amount');

        return [
            'withdrawals' => $withdrawals,
            'totalWithdrawn' => $totalWithdrawn,
        ];
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Bank Withdrawals</flux:heading>
            <flux:subheading size="lg" class="mb-6">Track money taken from the bank by company admins.</flux:subheading>
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Total Withdrawn: ${{ number_format($totalWithdrawn, 2) }}
            </div>
        </div>
        <flux:button href="{{ route('bank-withdrawals.create') }}" variant="primary">Record Withdrawal</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Admin</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
            <flux:table.column>Notes</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($withdrawals as $withdrawal)
                <flux:table.row>
                    <flux:table.cell>{{ $withdrawal->withdrawn_at->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($withdrawal->user->name) }}&color=7F9CF5&background=EBF4FF" size="sm" />
                            {{ $withdrawal->user->name }}
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>${{ number_format($withdrawal->amount, 2) }}</flux:table.cell>
                    <flux:table.cell>{{ $withdrawal->notes ?: '-' }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>