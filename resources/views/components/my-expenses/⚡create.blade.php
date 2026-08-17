<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Expense;
use App\Models\Supplier;
use App\Models\ExpenseType;
use App\Models\ExpenseStage;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public $receipt_no = '';

    #[Validate('required|string|max:255')]
    public $material = '';

    #[Validate('nullable|string')]
    public $description = '';

    #[Validate('nullable|exists:suppliers,id')]
    public $supplier_id = null;

    #[Validate('nullable|exists:expense_types,id')]
    public $expense_type_id = null;

    #[Validate('nullable|exists:expense_stages,id')]
    public $expense_stage_id = null;

    #[Validate('required|numeric|min:0.01')]
    public $amount = '';

    #[Validate('required|date')]
    public $spent_at = '';

    public function mount()
    {
        $this->spent_at = today()->format('Y-m-d');
    }

    public function with(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'expenseTypes' => ExpenseType::orderBy('name')->get(),
            'expenseStages' => ExpenseStage::orderBy('name')->get(),
        ];
    }

    public function save()
    {
        $this->validate();

        Expense::create([
            'staff_id' => Auth::id(),
            'receipt_no' => $this->receipt_no,
            'material' => $this->material,
            'description' => $this->description,
            'supplier_id' => $this->supplier_id ?: null,
            'expense_type_id' => $this->expense_type_id ?: null,
            'expense_stage_id' => $this->expense_stage_id ?: null,
            'amount' => $this->amount,
            'spent_at' => $this->spent_at,
        ]);

        return redirect()->route('my-expenses.index');
    }
};
?>

<div>
    <div class="mb-6 flex items-center gap-4">
        <flux:button href="{{ route('my-expenses.index') }}" variant="ghost" icon="arrow-left" class="!px-2" />
        <div>
            <flux:heading size="xl" level="1">Log Business Expense</flux:heading>
            <flux:subheading size="lg">Record a new expense against your allocated funds.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input 
                    wire:model="spent_at" 
                    label="Date of Expense" 
                    type="date" 
                    required 
                />

                <flux:input 
                    wire:model="receipt_no" 
                    label="Receipt No." 
                    placeholder="e.g. REC-12345" 
                    required 
                />
            </div>

            <flux:input 
                wire:model="material" 
                label="Material" 
                placeholder="e.g. Office Supplies" 
                required 
            />

            <flux:textarea 
                wire:model="description" 
                label="Description (Optional)" 
                placeholder="Detailed description of the expense..."
                rows="3" 
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:select wire:model="supplier_id" label="Supplier (Optional)" placeholder="Select a supplier">
                    @foreach($suppliers as $supplier)
                        <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="expense_type_id" label="Expense Type (Optional)" placeholder="Select a type">
                    @foreach($expenseTypes as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="expense_stage_id" label="Expense Stage (Optional)" placeholder="Select a stage">
                    @foreach($expenseStages as $stage)
                        <flux:select.option value="{{ $stage->id }}">{{ $stage->name }}</flux:select.option>
                    @endforeach
                </flux:select>

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
            </div>

            <div class="flex gap-4 pt-4">
                <flux:button type="submit" variant="primary">Log Expense</flux:button>
                <flux:button href="{{ route('my-expenses.index') }}" variant="ghost">Cancel</flux:button>
            </div>
        </div>
    </form>
</div>