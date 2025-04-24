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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('formatted');
            $table->string('region')->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('locality')->nullable();
            $table->string('street')->nullable();
            $table->integer('street_type')->nullable();
            $table->integer('street_type_number')->nullable();
            $table->string('house');
            $table->integer('entrance')->nullable();
            $table->integer('floor')->nullable();
            $table->string('apartment')->nullable();
            $table->string('orientation')->nullable();
            $table->integer('postal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
