<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commodities', function (Blueprint $table) {
            // Now replaced by: brands table (brand_id), commodity_variants table
            // (unit_of_measure + srp), and juridical_id (replaces free-text establishments).
            $table->dropColumn(['brand_name', 'establishments', 'unit_of_measure', 'srp']);
        });
    }

    public function down(): void
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->string('brand_name', 100)->nullable();
            $table->string('establishments', 100)->nullable();
            $table->string('unit_of_measure', 50)->default('1 kg');
            $table->decimal('srp', 10, 2)->nullable();
        });
    }
};