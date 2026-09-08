<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inward_case_id')->constrained('inward_cases')->cascadeOnDelete();
            $table->foreignUuid('calculation_run_id')->nullable()->constrained('calculation_runs')->nullOnDelete();
            
            $table->string('authority_number', 50)->unique();
            $table->string('authority_type', 20)->default('FP'); // 'FP', 'DLIS', 'LTA'
            $table->date('authority_date');
            
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('deductions_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            $table->decimal('dlis_amount', 15, 2)->default(0.00);
            
            $table->string('pdf_storage_path')->nullable();
            $table->string('verification_hash', 64)->nullable(); // SHA-256
            $table->uuid('digital_signature_id')->nullable();
            
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            
            $table->boolean('is_dispatched')->default(false);
            $table->timestamp('dispatched_at')->nullable();
            $table->string('dispatch_barcode', 50)->nullable();
            
            $table->boolean('is_uploaded_hrms')->default(false);
            $table->timestamp('hrms_uploaded_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorities');
    }
};
