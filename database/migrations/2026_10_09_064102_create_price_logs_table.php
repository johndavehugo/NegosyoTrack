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
        Schema::create('price_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('commodity_id')->index('idx_price_commodity');
            $table->decimal('prevailing_price', 10);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->unsignedBigInteger('monitored_by_agency_id')->nullable()->index('idx_monitored_agency');
            $table->timestamp('monitored_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_logs');
    }
};
