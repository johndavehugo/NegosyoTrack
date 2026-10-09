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
        Schema::create('employers', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('entity_no', 50)->unique('entity_no');
            $table->string('full_name', 200);
            $table->string('gender', 20)->nullable();
            $table->date('birth_date');
            $table->string('contact_no', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('special_category')->nullable();
            $table->string('address_id', 100)->nullable()->index('employers_ibfk_1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};
