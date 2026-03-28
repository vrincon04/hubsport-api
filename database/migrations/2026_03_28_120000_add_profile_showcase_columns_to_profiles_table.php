<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->json('stats')->nullable()->after('position');
            $table->json('experience')->nullable()->after('stats');
            $table->json('achievements')->nullable()->after('experience');
            $table->json('education')->nullable()->after('achievements');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['stats', 'experience', 'achievements', 'education']);
        });
    }
};
