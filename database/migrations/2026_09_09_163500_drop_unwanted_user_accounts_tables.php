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
        Schema::dropIfExists('user_accounts');
        Schema::dropIfExists('gpf_users_accounts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: user_accounts was an unmigrated legacy replica table replaced by the standard users table.
    }
};
