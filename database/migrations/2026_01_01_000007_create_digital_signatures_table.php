<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('authority_id')->nullable()->constrained('authorities')->cascadeOnDelete();
            $table->foreignId('signatory_user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('signatory_name', 255);
            $table->string('signatory_role', 100);
            $table->string('certificate_serial', 100);
            $table->string('certificate_issuer', 255);
            $table->longText('signed_hash'); // PKCS#7 / CMS block
            $table->timestamp('signed_at');
            $table->timestamp('certificate_valid_to')->nullable();
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
