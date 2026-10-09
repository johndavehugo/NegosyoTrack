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
        Schema::table('calamity_incident_businesses', function (Blueprint $table) {
            $table->foreign(['incident_id'], 'fk_ib_incident')->references(['id'])->on('calamity_incidents')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calamity_incident_businesses', function (Blueprint $table) {
            $table->dropForeign('fk_ib_incident');
        });
    }
};
