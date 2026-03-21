<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('due_day'); // 1-31
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('school_id');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_payment_plans');
    }
};
