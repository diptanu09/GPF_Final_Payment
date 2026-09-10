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
        Schema::table('case_nominees', function (Blueprint $table) {
            $table->string('beneficiary_code', 50)->nullable()->after('nominee_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_nominees', function (Blueprint $table) {
            $table->dropColumn('beneficiary_code');
        });
    }
};
