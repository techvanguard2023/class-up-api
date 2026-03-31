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
        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('instance_name')->unique();
            $table->string('instance_id')->nullable();
            $table->string('api_key')->nullable();
            $table->string('owner')->nullable();
            $table->string('profile_name')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->text('profile_status')->nullable();
            $table->enum('status', ['pending', 'connecting', 'connected', 'disconnected'])->default('pending');
            $table->string('server_url')->nullable();
            $table->string('integration')->default('WHATSAPP-BAILEYS');
            $table->text('webhook_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_instances');
    }
};
