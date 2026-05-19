<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN (?, ?) AND REFERENCED_TABLE_NAME IS NOT NULL',
            ['plant_stocks', 'plant_species_id', 'plant_variety_id']
        );

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE plant_stocks DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        Schema::table('plant_stocks', function (Blueprint $table): void {
            try {
                $table->dropIndex('plant_stocks_sample_idx');
            } catch (\Exception $e) {
                // index may not exist or be named differently; ignore
            }

            $table->dropColumn(['plant_species_id', 'plant_variety_id']);
        });

        Schema::table('plant_stocks', function (Blueprint $table): void {
            $table->index(['plant_sample_id', 'status'], 'plant_stocks_sample_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_stocks', function (Blueprint $table): void {
            try {
                $table->dropIndex('plant_stocks_sample_idx');
            } catch (\Exception $e) {
                // ignore if missing
            }

            $table->foreignId('plant_species_id')
                ->constrained('plant_species')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('plant_variety_id')
                ->nullable()
                ->constrained('plant_varieties')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index(['plant_species_id', 'plant_variety_id', 'plant_sample_id', 'status'], 'plant_stocks_sample_idx');
        });
    }
};
