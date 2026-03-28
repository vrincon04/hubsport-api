<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('job_offer_id')->constrained('job_offers')->onDelete('cascade');
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['job_offer_id', 'user_id']); // Prevent duplicate save
        });
    }

    public function down()
    {
        Schema::dropIfExists('saved_jobs');
    }
};
