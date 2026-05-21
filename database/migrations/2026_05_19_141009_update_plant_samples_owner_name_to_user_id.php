<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plant_samples', function (Blueprint $table) {

            // 1. Add new foreign key column
            $table->foreignId('user_id')
                ->nullable()
                ->after('plant_variety_id')
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        // 2. OPTIONAL: data migration (if owner_name = user name)
        // Comment out if not needed
        /*
        DB::table('plant_samples')
            ->join('users', 'users.name', '=', 'plant_samples.owner_name')
            ->update(['plant_samples.user_id' => DB::raw('users.id')]);
        */

        Schema::table('plant_samples', function (Blueprint $table) {

            // 3. Drop old column
            $table->dropColumn('owner_name');

            // 4. Index for performance
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('plant_samples', function (Blueprint $table) {

            // restore column
            $table->string('owner_name')->nullable()->after('plant_variety_id');

            $table->dropIndex(['user_id']);

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
