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
        Schema::create('vaults', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('name', 255)->nullable(false);
            $table->text('details')->nullable();
        });

        DB::statement('ALTER TABLE vaults ADD CONSTRAINT chk_vault_details_length CHECK ( CHAR_LENGTH(details) <= 5000 )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaults');
    }
};
