<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inward_cases', function (Blueprint $table) {
            $table->text('delay_justification')->nullable()->after('lta_to_whom');
            $table->foreignId('delay_approved_by')->nullable()->after('delay_justification')->constrained('users')->nullOnDelete();
            $table->timestamp('delay_approved_at')->nullable()->after('delay_approved_by');
        });

        Schema::table('calculation_runs', function (Blueprint $table) {
            $table->text('delay_justification')->nullable()->after('remarks');
            $table->foreignId('delay_approved_by')->nullable()->after('delay_justification')->constrained('users')->nullOnDelete();
            $table->integer('delay_months_count')->default(0)->after('delay_approved_by');
            $table->boolean('has_exceeded_delay_cap')->default(false)->after('delay_months_count');
        });
    }

    public function down(): void
    {
        Schema::table('calculation_runs', function (Blueprint $table) {
            $table->dropForeign(['delay_approved_by']);
            $table->dropColumn([
                'delay_justification',
                'delay_approved_by',
                'delay_months_count',
                'has_exceeded_delay_cap',
            ]);
        });

        Schema::table('inward_cases', function (Blueprint $table) {
            $table->dropForeign(['delay_approved_by']);
            $table->dropColumn([
                'delay_justification',
                'delay_approved_by',
                'delay_approved_at',
            ]);
        });
    }
};
