<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inward_cases', function (Blueprint $table) {
            $table->text('minus_balance_remarks')->nullable()->after('delay_approved_at');
            $table->decimal('amount_recovered', 15, 2)->default(0.00)->after('minus_balance_remarks');
            $table->timestamp('minus_balance_closed_at')->nullable()->after('amount_recovered');
            $table->text('cancelled_remarks')->nullable()->after('minus_balance_closed_at');
            $table->text('unapproved_remarks')->nullable()->after('cancelled_remarks');
            $table->text('transfer_remarks')->nullable()->after('unapproved_remarks');
            $table->foreignId('transferred_to_user_id')->nullable()->constrained('users')->nullOnDelete()->after('transfer_remarks');
        });
    }

    public function down(): void
    {
        Schema::table('inward_cases', function (Blueprint $table) {
            $table->dropForeign(['transferred_to_user_id']);
            $table->dropColumn([
                'minus_balance_remarks',
                'amount_recovered',
                'minus_balance_closed_at',
                'cancelled_remarks',
                'unapproved_remarks',
                'transfer_remarks',
                'transferred_to_user_id',
            ]);
        });
    }
};
