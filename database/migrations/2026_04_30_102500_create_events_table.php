<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUlid('sport_id')->nullable()->constrained('sports')->nullOnDelete();
            $table->string('name');
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('location');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('description');
            $table->string('photo_url')->nullable();
            $table->string('organizer_name');
            $table->string('organizer_contact');
            $table->unsignedInteger('participants_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
