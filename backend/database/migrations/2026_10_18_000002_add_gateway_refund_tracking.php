<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_created_at', 14)->nullable();
            $table->string('gateway_transaction_no', 20)->nullable();
            $table->string('refund_request_id', 32)->nullable()->unique();
            $table->string('refund_state', 20)->nullable();
            $table->string('gateway_response_code', 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn(['gateway_created_at', 'gateway_transaction_no', 'refund_request_id', 'refund_state', 'gateway_response_code']));
    }
};
