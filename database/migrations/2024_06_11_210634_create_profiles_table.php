<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->ulid('id')
                ->primary();
            $table->foreignUlid('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignUlid('country_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignUlid('sport_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number')
                ->unique()
                ->nullable();
            $table->string('bio')
                ->nullable();
            $table->date('birth_date')
                ->nullable();
            $table->string('position')
                ->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
