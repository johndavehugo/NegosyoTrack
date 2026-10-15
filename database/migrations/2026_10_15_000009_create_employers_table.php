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
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->string('id', 100)->primary();
            $table->string('entity_no', 50)->unique('entity_no');
            $table->string('full_name', 200);
            $table->string('gender', 20)->nullable();
            $table->date('birth_date');
            $table->string('contact_no', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('special_category', 255)->nullable();
            $table->string('address_id', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('address_id', 'employers_ibfk_1');
            $table->foreign('address_id', 'employers_ibfk_1')
                ->references('id')
                ->on('addresses')
                ->onUpdate('restrict')
                ->onDelete('set null');
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
