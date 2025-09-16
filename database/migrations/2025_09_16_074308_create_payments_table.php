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
        Schema::create('payments', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('offline');      // ex: stripe, paypal, offline
            $table->string('provider_ref')->nullable();          // ex: payment_intent id
            $table->integer('amount_cents');                     // montant en centimes
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('succeeded');      // pending|succeeded|failed|refunded
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();                 // détails bruts du PSP (JSON)
            $table->timestamps();

            $table->index(['provider','provider_ref']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
