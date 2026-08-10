<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAllocation extends Model
{
    protected $fillable = [
        'admin_id',
        'staff_id',
        'amount',
        'allocated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allocated_at' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
