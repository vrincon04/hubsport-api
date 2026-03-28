<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->foreignUlid('sport_id')->nullable()->constrained('sports')->onDelete('set null');
            $table->string('company');
            $table->string('location');
            $table->string('contract_type');
            $table->string('application_type')->default('simple'); // simple or web
            $table->string('application_url')->nullable();
            $table->text('description');
            $table->date('deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_offers');
    }
};
