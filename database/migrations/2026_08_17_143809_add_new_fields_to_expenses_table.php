<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('material')->after('amount');
            $table->text('description')->nullable()->after('material');
            $table->string('receipt_no')->after('id');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete()->after('staff_id');
            $table->foreignId('expense_type_id')->nullable()->constrained('expense_types')->nullOnDelete()->after('supplier_id');
            $table->foreignId('expense_stage_id')->nullable()->constrained('expense_stages')->nullOnDelete()->after('expense_type_id');
            
            $table->dropColumn(['purpose', 'notes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('purpose');
            $table->text('notes')->nullable();

            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['expense_type_id']);
            $table->dropForeign(['expense_stage_id']);
            
            $table->dropColumn([
                'material',
                'description',
                'receipt_no',
                'supplier_id',
                'expense_type_id',
                'expense_stage_id',
            ]);
        });
    }
};
