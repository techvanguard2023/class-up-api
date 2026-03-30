<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('student_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_payment_plan_id')->constrained()->onDelete('cascade');
            $table->integer('due_day')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('student_id');
            $table->index('school_payment_plan_id');
            $table->index('active');
            $table->unique(['student_id', 'school_payment_plan_id'], 'spp_student_plan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_payment_plans');
    }
};
