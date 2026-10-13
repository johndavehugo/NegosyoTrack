<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->foreignId('brand_id')
                ->nullable()
                ->after('category_id')
                ->constrained('brands')
                ->nullOnDelete();

            // juridicals.id is varchar(100), not bigint, so this is a string FK
            $table->string('juridical_id', 100)
                ->nullable()
                ->after('brand_id')
                ->constrained('juridicals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('juridical_id');
        });
    }
};