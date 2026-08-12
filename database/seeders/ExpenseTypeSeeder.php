<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExpenseType;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Material', 'Fuel', 'Wages', 'Advance', 'Other'];

        foreach ($types as $type) {
            ExpenseType::firstOrCreate(['name' => $type]);
        }
    }
}
