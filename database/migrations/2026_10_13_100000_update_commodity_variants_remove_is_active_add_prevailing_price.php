<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commodity_variants', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->decimal('prevailing_price', 10, 2)->nullable()->after('srp');
        });
    }

    public function down(): void
    {
        Schema::table('commodity_variants', function (Blueprint $table) {
            $table->dropColumn('prevailing_price');
            $table->boolean('is_active')->default(true)->after('srp');
        });
    }
};
