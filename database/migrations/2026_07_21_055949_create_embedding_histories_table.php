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
        Schema::create('embedding_histories', function (Blueprint $table) {

            $table->id();

            $table->longText('text');

            $table->string('model');

            $table->integer('embedding_length');

            $table->integer('tokens_used')->default(0);

            $table->boolean('is_mock')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embedding_histories');
    }
};
