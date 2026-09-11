<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved'); // approved, pending, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('designation', 150)->nullable();
            $table->string('section', 100)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('admin_notes')->nullable();
        });

        Schema::create('admin_security_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 50)->unique();
            $table->string('token_type', 30)->default('registration'); // registration, password_reset, all
            $table->string('role', 30)->nullable(); // auto-assign role
            $table->string('issued_for_email')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_used')->default(false);
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_security_tokens');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'designation',
                'section',
                'phone_number',
                'admin_notes',
            ]);
        });
    }
};
