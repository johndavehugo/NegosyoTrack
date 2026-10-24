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
        if (Schema::hasTable('commodity_variants')) {
            return;
        }

        Schema::create('commodity_variants', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('commodity_id');
            $table->string('unit_of_measure', 50);
            $table->decimal('srp', 10, 2)->nullable();
            $table->decimal('prevailing_price', 10, 2)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['commodity_id', 'unit_of_measure'], 'commodity_variants_commodity_id_unit_of_measure_unique');
            $table->foreign('commodity_id', 'commodity_variants_commodity_id_foreign')
                ->references('id')
                ->on('commodities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commodity_variants');
    }
};
