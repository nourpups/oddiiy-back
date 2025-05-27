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
        Schema::create('cashback_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedInteger('balance')->default(0);
            $table->unsignedInteger('total_earned')->default(0);
            $table->unsignedInteger('total_used')->default(0);
            $table->timestamps();
        });

        $this->createWalletsToExistingUsers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashback_wallets');
    }

    public function createWalletsToExistingUsers(): void
    {
        $userIds = DB::table('users')->select(['id as user_id'])->get();
        $userIds = $userIds->map(static fn (stdClass $userData) => [
            'user_id' => $userData->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cashback_wallets')->insert($userIds->toArray());
    }
};
