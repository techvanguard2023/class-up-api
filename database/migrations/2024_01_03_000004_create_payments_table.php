<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_payment_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->dateTime('due_date');
            $table->dateTime('paid_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'late', 'canceled'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('school_payment_plan_id');
            $table->index('payment_method_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('paid_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
