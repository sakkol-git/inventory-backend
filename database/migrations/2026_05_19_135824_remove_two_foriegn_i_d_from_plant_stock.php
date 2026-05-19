<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plant_stocks', function (Blueprint $table) {

            // drop index first (important)
            $table->dropIndex('plant_stocks_sample_idx');

            // drop foreign keys
            $table->dropForeign(['plant_species_id']);
            $table->dropForeign(['plant_variety_id']);

            // drop columns
            $table->dropColumn(['plant_species_id', 'plant_variety_id']);

            // re-add index without removed columns
            $table->index(
                ['plant_sample_id', 'status'],
                'plant_stocks_sample_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('plant_stocks', function (Blueprint $table) {

            // restore columns
            $table->foreignId('plant_species_id')
                ->constrained('plant_species')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('plant_variety_id')
                ->nullable()
                ->constrained('plant_varieties')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // restore index
            $table->dropIndex('plant_stocks_sample_idx');

            $table->index(
                ['plant_species_id', 'plant_variety_id', 'plant_sample_id', 'status'],
                'plant_stocks_sample_idx'
            );
        });
    }
};