<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inward_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('registration_no', 30)->unique()->index(); // e.g. "202301012345"
            $table->string('diary_number', 50)->nullable()->index();
            $table->date('diary_date')->nullable();
            
            // Legacy Oracle linkage
            $table->string('series_code', 10)->index();
            $table->string('series_name', 50)->nullable();
            $table->string('account_no', 30)->index();
            $table->string('subscriber_name_cache', 255)->index();
            $table->string('name_title', 20)->default('Shri');
            $table->string('designation_title', 20)->default('Dr/Mr/Mrs');
            $table->string('designation', 255)->nullable();
            
            // Case Category & Department details
            $table->string('case_type', 5)->default('F')->index(); // 'F', 'D', 'R', 'L', 'B', 'C'
            $table->string('pension_type_id', 10)->default('1')->index();
            $table->string('pension_type_name', 100)->nullable();
            $table->string('section', 50)->nullable();
            $table->string('ddo_code', 50)->nullable()->index();
            $table->string('ddo_designation', 255)->nullable();
            $table->string('treasury_code', 50)->nullable()->index();
            $table->string('treasury_name', 100)->nullable();
            $table->string('sub_treasury_name', 100)->nullable();
            
            // Demographic & Financial info
            $table->date('event_date')->nullable(); // Date of retirement / demise / effect
            $table->date('last_fund_deduction')->nullable();
            $table->decimal('debit_during_year', 15, 2)->default(0.00);
            $table->text('personal_address')->nullable();
            $table->string('mobile_no', 20)->nullable();
            $table->string('employee_code', 50)->nullable();
            $table->string('beneficiary_code', 50)->nullable();
            $table->string('spouse_name', 255)->nullable();
            $table->string('spouse_relation', 100)->nullable();
            $table->string('lta_to_whom', 255)->nullable();
            $table->date('date_of_lta')->nullable();

            // Status & Assigned Staff
            $table->integer('current_status')->default(1)->index(); // CaseWorkflowStatus enum
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Workflow Timestamps
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('pre_calculated_at')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('hrms_uploaded_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inward_cases');
    }
};
