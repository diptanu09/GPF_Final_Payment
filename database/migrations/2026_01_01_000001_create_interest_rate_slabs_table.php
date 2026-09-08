<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_rate_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('financial_year', 10)->index(); // e.g. "2023-2024"
            $table->integer('accounting_month')->index(); // 1 = April ... 12 = March
            $table->date('effective_from')->index();
            $table->date('effective_to')->index();
            $table->decimal('rate_percentage', 6, 4); // e.g. 7.1000
            $table->string('year_desc', 30)->nullable(); // e.g. "01-APR-2023"
            $table->string('notification_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_rate_slabs');
    }
};
