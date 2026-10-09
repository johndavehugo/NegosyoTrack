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
        Schema::create('calamity_incident_businesses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('incident_id')->index('idx_incident');
            $table->string('juridical_id', 100);
            $table->date('date_occurred')->nullable();
            $table->enum('nature_of_damage', ['PARTIAL', 'TOTAL'])->nullable();
            $table->enum('status', ['PENDING_VERIFICATION', 'VERIFIED', 'AID_RELEASED'])->nullable()->default('VERIFIED');
            $table->decimal('estimated_cost_of_damages', 12)->default(0);
            $table->text('remarks')->nullable();

            $table->unique(['incident_id', 'juridical_id'], 'uq_incident_business');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calamity_incident_businesses');
    }
};
