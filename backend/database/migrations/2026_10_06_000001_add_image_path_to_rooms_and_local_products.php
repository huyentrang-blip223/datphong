<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'image_path')) {
                $table->string('image_path')->nullable()->after('active');
            }
        });

        Schema::table('local_products', function (Blueprint $table) {
            if (!Schema::hasColumn('local_products', 'image_path')) {
                $table->string('image_path')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });

        Schema::table('local_products', function (Blueprint $table) {
            if (Schema::hasColumn('local_products', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
