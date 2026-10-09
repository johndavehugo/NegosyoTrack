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
        Schema::create('calamity_incidents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('juridical_id', 100)->index('idx_juridical');
            $table->unsignedBigInteger('calamity_id')->index('idx_calamity');
            $table->date('date_occurred');
            $table->enum('nature_of_damage', ['PARTIAL', 'TOTAL']);
            $table->decimal('estimated_cost_of_damages', 12);
            $table->text('remarks')->nullable();
            $table->enum('status', ['PENDING_VERIFICATION', 'VERIFIED', 'AID_RELEASED'])->nullable()->default('VERIFIED');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->index(['date_occurred', 'nature_of_damage'], 'idx_damage_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calamity_incidents');
    }
};
