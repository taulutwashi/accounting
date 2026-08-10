<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'staff_id',
        'amount',
        'purpose',
        'spent_at',
        'notes',
    ];

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
