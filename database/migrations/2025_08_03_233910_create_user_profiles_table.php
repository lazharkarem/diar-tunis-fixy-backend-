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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('bio')->nullable();
            $table->enum('id_verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('bank_account_details')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('service_areas')->nullable();
            $table->decimal('response_rate', 5, 2)->nullable();
            $table->timestamp('last_active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
