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
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->string('id', 100)->primary();
            $table->string('entity_no', 50)->unique('entity_no');
            $table->string('employer_id', 100);
            $table->string('address_id', 100)->nullable();
            $table->string('name', 200)->index('idx_business_name');
            $table->dateTime('date_reg');
            $table->string('registration_type', 50)->nullable()->default('NEW');
            $table->string('bus_status', 30)->nullable()->default('ACTIVE');
            $table->string('contact_no', 30)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('line_of_industry', 150)->nullable();
            $table->decimal('capitalization', 15, 2)->default(0);
            $table->enum('category', ['MICRO', 'SMALL', 'MEDIUM', 'LARGE'])
                ->nullable()
                ->storedAs("case when `capitalization` <= 3000000.00 then 'MICRO' when `capitalization` <= 15000000.00 then 'SMALL' when `capitalization` <= 100000000.00 then 'MEDIUM' else 'LARGE' end");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('employer_id', 'juridicals_ibfk_1');
            $table->index('address_id', 'juridicals_ibfk_2');
            $table->foreign('employer_id', 'juridicals_ibfk_1')
                ->references('id')
                ->on('employers')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('address_id', 'juridicals_ibfk_2')
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
        Schema::dropIfExists('juridicals');
    }
};
