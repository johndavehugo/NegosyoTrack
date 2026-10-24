<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SCIMS data is copied onto our records so lists stay fast, searchable,
        // sortable and usable while the API is unavailable. The refresh actions
        // on the training/trainee screens update these snapshots.
        if (Schema::hasTable('trainings') && ! Schema::hasColumn('trainings', 'business_name')) {
            Schema::table('trainings', function (Blueprint $table) {
                $table->string('business_name', 300)->nullable()->after('entity_no');
            });
        }

        // These columns are guarded because the trainees table was created
        // outside the migrations on older installs.
        if (Schema::hasTable('trainees')) {
            Schema::table('trainees', function (Blueprint $table) {
                if (! Schema::hasColumn('trainees', 'full_name')) {
                    $table->string('full_name', 255)->nullable()->after('api_entity_no');
                }

                if (! Schema::hasColumn('trainees', 'gender')) {
                    $table->string('gender', 30)->nullable()->after('full_name');
                }

                if (! Schema::hasColumn('trainees', 'birth_date')) {
                    $table->date('birth_date')->nullable()->after('gender');
                }

                if (! Schema::hasColumn('trainees', 'mobile_no')) {
                    $table->string('mobile_no', 50)->nullable()->after('birth_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trainings') && Schema::hasColumn('trainings', 'business_name')) {
            Schema::table('trainings', function (Blueprint $table) {
                $table->dropColumn('business_name');
            });
        }

        if (Schema::hasTable('trainees')) {
            $columns = array_values(array_filter(
                ['full_name', 'gender', 'birth_date', 'mobile_no'],
                fn (string $column): bool => Schema::hasColumn('trainees', $column),
            ));

            if ($columns !== []) {
                Schema::table('trainees', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
