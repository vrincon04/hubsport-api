<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->uuid('id')
                ->primary();
            $table->foreignUuid('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_id');
            $table->text('provider_token');
            $table->text('provider_refresh_token')
                ->nullable();
            $table->unique([ 'provider_id','provider' ]);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
