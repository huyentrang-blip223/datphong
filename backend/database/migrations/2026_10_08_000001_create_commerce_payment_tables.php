<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_orders') && ! Schema::hasTable('product_order_payments')) {
            Schema::create('product_order_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('product_orders')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('gateway', 40);
                $table->string('txn_ref', 80)->unique();
                $table->decimal('amount', 14, 2);
                $table->enum('status', ['initiated', 'paid', 'failed', 'refunded'])->default('initiated');
                $table->dateTime('paid_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['order_id', 'status']);
            });
        }

        if (Schema::hasTable('experience_bookings') && ! Schema::hasTable('experience_booking_payments')) {
            Schema::create('experience_booking_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('experience_booking_id')->constrained('experience_bookings')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('gateway', 40);
                $table->string('txn_ref', 80)->unique();
                $table->decimal('amount', 14, 2);
                $table->enum('status', ['initiated', 'paid', 'failed', 'refunded'])->default('initiated');
                $table->dateTime('paid_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['experience_booking_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_booking_payments');
        Schema::dropIfExists('product_order_payments');
    }
};
