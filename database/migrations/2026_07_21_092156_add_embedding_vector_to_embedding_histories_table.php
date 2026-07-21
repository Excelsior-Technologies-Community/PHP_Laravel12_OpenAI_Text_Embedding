<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embedding_histories', function (Blueprint $table) {

            $table->longText('embedding_vector')
                ->nullable()
                ->after('embedding_length');

        });
    }

    public function down(): void
    {
        Schema::table('embedding_histories', function (Blueprint $table) {

            $table->dropColumn('embedding_vector');

        });
    }
};