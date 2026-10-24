<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded because trainees existed as a hand-made table (with data)
        // on installs that predate this migration.
        if (Schema::hasTable('trainees')) {
            return;
        }

        Schema::create('trainees', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('id')->primary()->collation('utf8mb4_general_ci');
            $table->string('api_entity_no')->unique()->collation('utf8mb4_general_ci');
            $table->string('full_name')->nullable();
            $table->string('gender', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('mobile_no', 50)->nullable();
            $table->string('training_id', 100)->collation('utf8mb4_general_ci');
            $table->string('contact_no');
            $table->foreign('training_id')->references('id')->on('trainings');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};
