<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculation_monthly_breakdowns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_run_id')->constrained('calculation_runs')->cascadeOnDelete();
            
            $table->string('financial_year', 10)->index();
            $table->string('calendar_month', 10); // e.g. "2023-04"
            $table->date('pay_slip_date')->index();
            $table->date('interest_date')->nullable();
            $table->integer('accounting_month'); // 1 = April ... 12 = March
            
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('deposit', 15, 2)->default(0.00);
            $table->decimal('withdrawal', 15, 2)->default(0.00);
            $table->decimal('rate_of_interest', 6, 4)->default(0.0000);
            $table->boolean('interest_on_deposit')->default(true);
            
            $table->decimal('progressive_balance', 15, 2)->default(0.00);
            $table->decimal('actual_interest', 15, 2)->default(0.00);
            $table->decimal('delay_interest', 15, 2)->default(0.00);
            
            $table->boolean('is_cut_month')->default(false);
            $table->boolean('is_adjustment')->default(false);
            $table->string('voucher_no', 50)->nullable();
            $table->string('abstract_no', 50)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_monthly_breakdowns');
    }
};
