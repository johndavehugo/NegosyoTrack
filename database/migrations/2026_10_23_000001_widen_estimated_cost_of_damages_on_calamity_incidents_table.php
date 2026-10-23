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
        Schema::table('calamity_incidents', function (Blueprint $table) {
            $table->decimal('estimated_cost_of_damages', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calamity_incidents', function (Blueprint $table) {
            $table->decimal('estimated_cost_of_damages', 12, 2)->change();
        });
    }
};
