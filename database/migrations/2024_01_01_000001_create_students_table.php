<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('modality');
            $table->string('outside_school_name')->nullable();
            $table->string('level');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('next_class')->nullable(); // Could be a relationship or computed
            $table->string('photo_url')->nullable();
            $table->float('attendance_rate')->default(0);
            $table->date('birth_date')->nullable();
            $table->json('health_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
