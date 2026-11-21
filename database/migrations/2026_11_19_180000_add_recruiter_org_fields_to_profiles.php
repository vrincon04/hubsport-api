<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('organization_name')->nullable()->after('current_team');
            $table->string('rnc', 40)->nullable()->after('organization_name');
            $table->string('organization_document_url')->nullable()->after('rnc');
            $table->string('organization_website')->nullable()->after('organization_document_url');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'organization_name',
                'rnc',
                'organization_document_url',
                'organization_website',
            ]);
        });
    }
};
