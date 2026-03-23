<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->string('team_a');
            $table->string('team_b');
            $table->integer('score_a')->default(0);
            $table->integer('score_b')->default(0);
            $table->dateTime('match_date');
            $table->string('status')->default('completed'); // live, upcoming, completed
            $table->foreignUlid('sport_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
