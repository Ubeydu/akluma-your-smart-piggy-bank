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
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->renameColumn('image', 'preview_image');
            $table->string('preview_title', 1000)->nullable();
            $table->string('preview_description', 1000)->nullable();
            $table->string('preview_url', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piggy_banks', function (Blueprint $table) {
            $table->renameColumn('preview_image', 'image');
            $table->dropColumn(['preview_title', 'preview_description', 'preview_url']);
        });
    }
};
