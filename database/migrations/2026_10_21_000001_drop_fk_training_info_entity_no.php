<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK to juridicals — entity_no is now a standalone string field
        // managed by our own database. Guarded so it only runs when the table
        // and the constraint actually exist (e.g. not on migrate:fresh installs).
        if (! Schema::hasTable('trainings')) {
            return;
        }

        $hasEntityNoForeignKey = collect(Schema::getForeignKeys('trainings'))
            ->contains(fn (array $foreignKey): bool => in_array('entity_no', $foreignKey['columns'], strict: true));

        if (! $hasEntityNoForeignKey) {
            return;
        }

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropForeign(['entity_no']);
        });
    }

    public function down(): void
    {
        // Not re-adding the FK — juridicals table may not exist
    }
};
