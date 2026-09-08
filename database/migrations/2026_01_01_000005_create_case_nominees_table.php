<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_nominees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inward_case_id')->constrained('inward_cases')->cascadeOnDelete();
            
            $table->string('nominee_name', 255);
            $table->string('relationship', 100);
            $table->decimal('share_percentage', 5, 2); // e.g. 50.00
            $table->decimal('allocated_amount', 15, 2)->default(0.00);
            
            $table->string('bank_account_no', 50)->nullable();
            $table->string('bank_ifsc', 20)->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->string('marital_status', 50)->nullable();
            $table->string('guardian_name', 255)->nullable();
            $table->boolean('is_minor')->default(false);
            $table->text('address')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_nominees');
    }
};
