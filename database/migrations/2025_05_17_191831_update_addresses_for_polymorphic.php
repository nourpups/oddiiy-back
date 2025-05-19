<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $name = 'addressable';

            $table->string("{$name}_type")->after('id');

            $table->unsignedBigInteger("{$name}_id")->after("{$name}_type");

            $table->index(["{$name}_type", "{$name}_id"]);
        });

        DB::statement("UPDATE addresses SET addressable_id = user_id, addressable_type = 'App\\\\Models\\\\User'");

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Восстанавливаем старую колонку
            $table->foreignId('user_id')->nullable()->constrained();
        });

        // Восстанавливаем существующие данные
        DB::statement("UPDATE addresses SET user_id = addressable_id WHERE addressable_type = 'App\\\\Models\\\\User'");

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropMorphs('addressable');
        });
    }
};
