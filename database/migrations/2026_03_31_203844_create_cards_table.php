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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiat_wallet_id')->constrained()->onDelete('cascade');
            $table->string('card_number')->unique();
            $table->string('cvv');
            $table->date('expiry_date');
            $table->string('card_type')->default('Visa');
            $table->boolean('is_active')->default(true);
            $table->boolean('contactless_enabled')->default(true);
            $table->boolean('online_payment_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
