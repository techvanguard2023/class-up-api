<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', ['active', 'canceled', 'expired', 'trial', 'past_due'])
                ->default('active')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('status', ['active', 'canceled', 'expired', 'trial'])
                ->default('active')
                ->change();
        });
    }
};
