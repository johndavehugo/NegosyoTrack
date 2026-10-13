<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One commodity (product) can have many variants:
        // e.g. "555 Sardines" -> 150g variant, 230g variant, each with its own SRP.
        Schema::create('commodity_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commodity_id')
                ->constrained('commodities')
                ->cascadeOnDelete();

            $table->string('unit_of_measure', 50); // e.g. "150 g", "1 kg", "Per Liter"
            $table->decimal('srp', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['commodity_id', 'unit_of_measure']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commodity_variants');
    }
};