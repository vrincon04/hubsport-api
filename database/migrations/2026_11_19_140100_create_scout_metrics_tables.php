<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_metric_definitions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->string('metric_group', 80);
            $table->string('key', 80);
            $table->string('label');
            $table->string('unit', 40)->nullable();
            $table->boolean('lower_is_better')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sport_id', 'key']);
            $table->index(['sport_id', 'metric_group']);
        });

        Schema::create('athlete_metric_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUlid('definition_id')->constrained('sport_metric_definitions')->cascadeOnDelete();
            $table->decimal('value_numeric', 12, 4)->nullable();
            $table->string('value_text')->nullable();
            $table->date('recorded_at')->nullable();
            $table->string('source', 40)->default('self_reported');
            $table->string('source_url')->nullable();
            $table->string('verification_status', 40)->default('self_reported');
            $table->timestamps();

            $table->unique(['profile_id', 'definition_id']);
        });

        Schema::create('athlete_competition_results', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->date('competed_at');
            $table->string('competition');
            $table->string('mark')->nullable();
            $table->string('placement')->nullable();
            $table->string('official_url')->nullable();
            $table->string('verification_status', 40)->default('self_reported');
            $table->timestamps();
        });

        Schema::create('athlete_videos', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('title');
            $table->string('video_url');
            $table->string('kind', 40)->default('game');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athlete_videos');
        Schema::dropIfExists('athlete_competition_results');
        Schema::dropIfExists('athlete_metric_values');
        Schema::dropIfExists('sport_metric_definitions');
    }
};
