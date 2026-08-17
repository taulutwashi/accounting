<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'receipt_no',
        'staff_id',
        'supplier_id',
        'expense_type_id',
        'expense_stage_id',
        'amount',
        'material',
        'description',
        'spent_at',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function type()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }

    public function stage()
    {
        return $this->belongsTo(ExpenseStage::class, 'expense_stage_id');
    }

    protected function casts(): array
    {
        return [
            'spent_at' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
