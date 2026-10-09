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
        Schema::create('juridicals', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('entity_no', 50)->unique('entity_no');
            $table->string('employer_id', 100)->index('juridicals_ibfk_1');
            $table->string('address_id', 100)->nullable()->index('juridicals_ibfk_2');
            $table->string('name', 200)->index('idx_business_name');
            $table->dateTime('date_reg');
            $table->string('registration_type', 50)->nullable()->default('NEW');
            $table->string('bus_status', 30)->nullable()->default('ACTIVE');
            $table->string('contact_no', 30)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('line_of_industry', 150)->nullable();
            $table->decimal('capitalization', 15)->default(0);
            $table->enum('category', ['MICRO', 'SMALL', 'MEDIUM', 'LARGE'])->nullable()->storedAs('case when `capitalization` <= 3000000.00 then \'MICRO\' when `capitalization` <= 15000000.00 then \'SMALL\' when `capitalization` <= 100000000.00 then \'MEDIUM\' else \'LARGE\' end');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('juridicals');
    }
};
