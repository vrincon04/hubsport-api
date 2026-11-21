<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('account_status', 40)->default('active')->after('profile_type');
            $table->string('guardian_email')->nullable()->after('account_status');
            $table->timestamp('guardian_confirmed_at')->nullable()->after('guardian_email');
            $table->string('guardian_token', 64)->nullable()->after('guardian_confirmed_at');
        });

        Schema::create('user_reports', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('conversations')->nullOnDelete();
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['reason', 'created_at']);
        });

        Schema::create('minor_message_audits', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->foreignUlid('adult_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('minor_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['minor_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minor_message_audits');
        Schema::dropIfExists('user_reports');
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'account_status',
                'guardian_email',
                'guardian_confirmed_at',
                'guardian_token',
            ]);
        });
    }
};
