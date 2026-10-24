<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The trainings table was created manually on early installs, before
        // this migration existed. Guard so those databases can still migrate.
        if (Schema::hasTable('trainings')) {
            return;
        }

        Schema::create('trainings', function (Blueprint $table) {
            $table->string('id', 100)->primary()->collation('utf8mb4_general_ci'); // trin-{uuid}
            $table->string('entity_no', 50)->collation('utf8mb4_general_ci')->index(); // standalone — no FK
            $table->string('training_program', 300);         // full program name (free text)
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedInteger('hours_no');
            $table->string('training_type', 150);
            $table->string('conducted_by', 300);
            $table->string('sector', 100)->nullable();
            $table->date('graduated_at')->nullable();
            $table->string('status', 50)->default('COMPLETED');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
