<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email', 180)->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
        Schema::table('product_orders', function (Blueprint $table) {
            $table->unsignedInteger('points_redeemed')->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
        });
        Schema::create('cart_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->uuid('checkout_key')->unique();
            $table->json('order_ids');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_checkouts');
        Schema::table('product_orders', function (Blueprint $table) {
        $table->dropColumn(['points_redeemed', 'discount_amount']);
        });
        // password_resets may predate this migration; preserve its data.
    }
};
