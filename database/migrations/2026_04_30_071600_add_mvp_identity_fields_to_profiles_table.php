<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('profile_type')->default('athlete')->after('sport_id');
            $table->string('city')->nullable()->after('country_id');
            $table->string('sport_level')->nullable()->after('position');
            $table->string('current_team')->nullable()->after('sport_level');
            $table->json('social_links')->nullable()->after('current_team');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_number');
            $table->string('verification_status')->default('unverified')->after('phone_verified_at');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->boolean('verified_badge')->default(false)->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'profile_type',
                'city',
                'sport_level',
                'current_team',
                'social_links',
                'phone_verified_at',
                'verification_status',
                'verified_at',
                'verified_badge',
            ]);
        });
    }
};
