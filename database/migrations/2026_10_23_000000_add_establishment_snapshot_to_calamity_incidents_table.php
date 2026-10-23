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
            $table->string('juridical_name', 255)->nullable()->after('juridical_id');
            $table->string('barangay', 100)->nullable()->after('juridical_name');
            $table->index('barangay', 'idx_incident_barangay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calamity_incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incident_barangay');
            $table->dropColumn(['juridical_name', 'barangay']);
        });
    }
};
