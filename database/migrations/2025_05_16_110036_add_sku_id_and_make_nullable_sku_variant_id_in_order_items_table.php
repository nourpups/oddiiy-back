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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('sku_id')->after('order_id')->constrained();
            $table->unsignedBigInteger('sku_variant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['sku_id']);
            $table->dropColumn('sku_id');
            $table->unsignedBigInteger('sku_variant_id')->change();
        });
    }
};
