<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship_type');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('requester_confirmed_at')->nullable();
            $table->timestamp('recipient_confirmed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['requester_id', 'status']);
            $table->index(['recipient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_requests');
    }
};
