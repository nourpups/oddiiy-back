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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('recipient_name')->after('coupon_id');
            // меняем comment
            $table->unsignedBigInteger('coupon_id')->nullable()->change();
            $table->text('comment')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE `orders` SET `comment` = 'pusto' WHERE `comment` IS NULL");
        DB::statement("UPDATE `orders` SET `coupon_id` = 0 WHERE `coupon_id` IS NULL");
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('recipient_name');
            //  меняем comment
            $table->unsignedBigInteger('coupon_id')->change();
            $table->text('comment')->change();
        });
    }
};
