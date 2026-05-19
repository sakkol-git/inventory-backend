<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plant_stocks', function (Blueprint $table) {

            // 1. drop foreign key first (IMPORTANT)
            $table->dropForeign('plant_stocks_plant_sample_id_foreign');
        });

        // 2. now safe to drop index
        DB::statement('DROP INDEX ps_sample_variety_idx ON plant_stocks');

        Schema::table('plant_stocks', function (Blueprint $table) {

            // 3. re-create clean index
            $table->index(
                ['plant_sample_id', 'status'],
                'plant_stocks_sample_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('plant_stocks', function (Blueprint $table) {

            $table->index('ps_sample_variety_idx');

            $table->foreign('plant_sample_id')
                ->references('id')
                ->on('plant_samples')
                ->nullOnDelete();
        });
    }
};