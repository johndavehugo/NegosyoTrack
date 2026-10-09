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
        Schema::create('calamities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 150);
            $table->enum('calamity_type', ['TYPHOON', 'FLOOD', 'EARTHQUAKE', 'FIRE', 'LANDSLIDE', 'OTHER']);
            $table->date('declaration_date');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calamities');
    }
};
