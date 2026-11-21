<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->decimal('height_cm', 5, 2)->nullable()->after('birth_date');
            $table->decimal('weight_kg', 5, 2)->nullable()->after('height_cm');
            $table->decimal('wingspan_cm', 5, 2)->nullable()->after('weight_kg');
            $table->string('laterality', 20)->nullable()->after('wingspan_cm');
            $table->json('physical_extras')->nullable()->after('laterality');
            $table->timestamp('sport_locked_at')->nullable()->after('sport_id');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'height_cm',
                'weight_kg',
                'wingspan_cm',
                'laterality',
                'physical_extras',
                'sport_locked_at',
            ]);
        });
    }
};
