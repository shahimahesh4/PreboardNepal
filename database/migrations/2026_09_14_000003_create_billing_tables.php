<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('description');
            $t->unsignedInteger('amount_paisa');
            $t->unsignedInteger('duration_days');
            $t->boolean('is_active')->default(false);
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->uuid('reference')->unique();
            $t->uuid('idempotency_key')->unique();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->string('title');
            $t->unsignedInteger('amount_paisa');
            $t->unsignedInteger('duration_days');
            $t->string('environment');
            $t->string('status')->default('created')->index();
            $t->string('provider_reference')->nullable()->unique();
            $t->text('payment_url')->nullable();
            $t->string('transaction_id')->nullable()->unique();
            $t->timestamp('verified_at')->nullable();
            $t->timestamp('last_checked_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
