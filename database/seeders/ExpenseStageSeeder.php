<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = ['Mobilization', 'Excavation', 'Foundation', 'Ground Floor', 'First Floor', 'Electrical', 'Plumbing', 'Painting', 'Roofing', 'Finishing', 'Other'];

        foreach ($stages as $stage) {
            \App\Models\ExpenseStage::firstOrCreate(['name' => $stage]);
        }
    }
}
