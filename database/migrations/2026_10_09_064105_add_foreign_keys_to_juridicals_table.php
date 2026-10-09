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
        Schema::table('juridicals', function (Blueprint $table) {
            $table->foreign(['employer_id'], 'juridicals_ibfk_1')->references(['id'])->on('employers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['address_id'], 'juridicals_ibfk_2')->references(['id'])->on('addresses')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('juridicals', function (Blueprint $table) {
            $table->dropForeign('juridicals_ibfk_1');
            $table->dropForeign('juridicals_ibfk_2');
        });
    }
};
