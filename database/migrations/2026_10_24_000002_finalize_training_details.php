<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The table was first created by hand without an auto-incrementing
        // key or timestamps, which makes Eloquent inserts impossible.
        if (! Schema::hasTable('training_details')) {
            Schema::create('training_details', function (Blueprint $table) {
                $table->increments('id');
                $table->date('graduated_at')->nullable();
                $table->string('status', 50)->default('COMPLETED');
                $table->timestamps();
            });
        } else {
            $id = DB::selectOne("SHOW COLUMNS FROM training_details WHERE Field = 'id'");

            if ($id && ! str_contains((string) $id->Extra, 'auto_increment')) {
                DB::statement('ALTER TABLE training_details MODIFY id INT NOT NULL AUTO_INCREMENT');
            }

            Schema::table('training_details', function (Blueprint $table) {
                $table->date('graduated_at')->nullable()->change();
                $table->string('status', 50)->default('COMPLETED')->change();
            });

            if (! Schema::hasColumn('training_details', 'created_at')) {
                Schema::table('training_details', function (Blueprint $table) {
                    $table->timestamps();
                });
            }
        }

        // One detail row belongs to exactly one trainee. MySQL permits any
        // number of NULLs in a unique index, so unmarked trainees stay NULL.
        if (Schema::hasTable('trainees') && Schema::hasColumn('trainees', 'training_details_id')) {
            $hasUnique = collect(Schema::getIndexes('trainees'))
                ->contains(fn (array $index): bool => $index['unique']
                    && in_array('training_details_id', $index['columns'], true));

            if (! $hasUnique) {
                Schema::table('trainees', function (Blueprint $table) {
                    $table->unique('training_details_id');
                });
            }

            // Deleting a completion record should simply unlink the trainee
            // instead of failing on the foreign key.
            $foreignKey = collect(Schema::getForeignKeys('trainees'))
                ->first(fn (array $foreignKey): bool => in_array('training_details_id', $foreignKey['columns'], true));

            if ($foreignKey && strtolower((string) $foreignKey['on_delete']) !== 'set null') {
                Schema::table('trainees', function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey['name']);
                });

                Schema::table('trainees', function (Blueprint $table) {
                    $table->foreign('training_details_id')
                        ->references('id')
                        ->on('training_details')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trainees')) {
            $hasUnique = collect(Schema::getIndexes('trainees'))
                ->contains(fn (array $index): bool => $index['unique']
                    && in_array('training_details_id', $index['columns'], true));

            if ($hasUnique) {
                Schema::table('trainees', function (Blueprint $table) {
                    $table->dropUnique(['training_details_id']);
                });
            }
        }

        if (! Schema::hasTable('training_details')) {
            return;
        }

        if (Schema::hasColumn('training_details', 'created_at')) {
            Schema::table('training_details', function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
