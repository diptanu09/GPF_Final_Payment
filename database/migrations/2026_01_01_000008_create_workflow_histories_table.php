<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('inward_case_id')->constrained('inward_cases')->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->integer('from_status')->nullable();
            $table->integer('to_status');
            $table->string('action_type', 50); // 'SUBMIT', 'RECOMMEND', 'REVERT', 'APPROVE', 'AUTHORIZE', 'DISPATCH'
            $table->text('remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_histories');
    }
};
