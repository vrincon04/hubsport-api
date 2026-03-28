<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('job_offer_id')->constrained('job_offers')->onDelete('cascade');
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, reviewed, rejected, accepted
            $table->timestamps();
            
            $table->unique(['job_offer_id', 'user_id']); // Prevent duplicate applications
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_applications');
    }
};
