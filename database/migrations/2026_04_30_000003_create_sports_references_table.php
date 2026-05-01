<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sports_references', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('reference_request_id')->nullable()->constrained('reference_requests')->nullOnDelete();
            $table->foreignUlid('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('subject_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship_type');
            $table->text('body');
            $table->string('status')->default('pending_confirmation');
            $table->boolean('is_suspicious')->default(false);
            $table->string('suspicious_reason')->nullable();
            $table->timestamps();

            $table->index(['subject_user_id', 'status']);
            $table->index(['author_id', 'subject_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sports_references');
    }
};
