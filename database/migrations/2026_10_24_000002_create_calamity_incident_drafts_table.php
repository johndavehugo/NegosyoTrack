<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calamity_incident_drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid('draft_key')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('calamity_id')->index();
            $table->string('juridical_id');
            $table->string('juridical_name')->nullable();
            $table->string('barangay')->nullable();
            $table->decimal('estimated_cost_of_damages', 15, 2)->nullable();
            $table->json('photos')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['draft_key', 'juridical_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calamity_incident_drafts');
    }
};
