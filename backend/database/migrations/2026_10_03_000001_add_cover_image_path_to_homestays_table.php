<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('homestays') && ! Schema::hasColumn('homestays', 'cover_image_path')) {
            Schema::table('homestays', function (Blueprint $table) {
                $table->string('cover_image_path', 255)->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('homestays') && Schema::hasColumn('homestays', 'cover_image_path')) {
            Schema::table('homestays', function (Blueprint $table) {
                $table->dropColumn('cover_image_path');
            });
        }
    }
};
