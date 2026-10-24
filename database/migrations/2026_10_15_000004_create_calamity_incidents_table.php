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
        if (Schema::hasTable('calamity_incidents')) {
            return;
        }

        Schema::create('calamity_incidents', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->string('juridical_id', 100);
            $table->unsignedBigInteger('calamity_id');
            $table->date('date_occurred');
            $table->enum('nature_of_damage', ['PARTIAL', 'TOTAL']);
            $table->decimal('estimated_cost_of_damages', 12, 2);
            $table->text('remarks')->nullable();
            $table->enum('status', ['PENDING_VERIFICATION', 'VERIFIED', 'AID_RELEASED'])->default('VERIFIED');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('juridical_id', 'idx_juridical');
            $table->index('calamity_id', 'idx_calamity');
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
