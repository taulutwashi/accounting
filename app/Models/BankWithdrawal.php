<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankWithdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'withdrawn_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'withdrawn_at' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
