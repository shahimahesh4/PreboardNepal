<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('provider')->default('khalti')->index();
            $table->string('merchant_code')->nullable();
            $table->dropUnique(['provider_reference']);
            $table->dropUnique(['transaction_id']);
            $table->unique(['provider', 'environment', 'provider_reference'], 'orders_gateway_reference_unique');
            $table->unique(['provider', 'environment', 'transaction_id'], 'orders_gateway_transaction_unique');
        });
    }

    public function down(): void
    {
        // Removing provider identity would make existing multi-gateway transactions ambiguous.
        throw new RuntimeException('This data-preserving migration requires a reviewed rollback plan.');
    }
};
