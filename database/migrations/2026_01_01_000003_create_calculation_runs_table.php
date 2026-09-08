<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculation_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inward_case_id')->constrained('inward_cases')->cascadeOnDelete();
            
            $table->string('opening_fin_year', 10);
            $table->decimal('opening_balance_amount', 15, 2)->default(0.00);
            $table->decimal('total_subscriptions', 15, 2)->default(0.00);
            $table->decimal('total_refunds', 15, 2)->default(0.00);
            $table->decimal('total_withdrawals', 15, 2)->default(0.00);
            $table->decimal('excess_deposits', 15, 2)->default(0.00);
            $table->decimal('missing_credits_total', 15, 2)->default(0.00);
            $table->decimal('missing_debits_total', 15, 2)->default(0.00);
            $table->decimal('actual_interest_computed', 15, 2)->default(0.00);
            $table->decimal('delayed_interest_computed', 15, 2)->default(0.00);
            $table->decimal('total_interest_computed', 15, 2)->default(0.00);
            
            // DLIS
            $table->boolean('dlis_admissible')->default(false);
            $table->decimal('dlis_amount', 15, 2)->default(0.00);
            
            // Final Payable
            $table->decimal('final_closing_balance', 15, 2)->default(0.00);
            $table->date('cutoff_date')->nullable();
            $table->date('interest_allowed_upto')->nullable();
            
            $table->foreignId('computed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->boolean('is_locked')->default(false);
            $table->text('remarks')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_runs');
    }
};
