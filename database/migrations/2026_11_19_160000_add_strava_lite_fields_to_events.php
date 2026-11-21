<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_type')->nullable()->after('name');
            $table->unsignedInteger('capacity')->nullable()->after('participants_count');
            $table->string('route_text')->nullable()->after('location');
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->string('activity_url')->nullable()->after('status');
            $table->string('activity_note')->nullable()->after('activity_url');
            $table->timestamp('linked_at')->nullable()->after('activity_note');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'capacity', 'route_text']);
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn(['activity_url', 'activity_note', 'linked_at']);
        });
    }
};
