<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La columna y el índice se agregan por separado para que MySQL pueda
        // resolver el ALTER de la columna sin reconstruir la tabla.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('is_demo', 'users_is_demo_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_is_demo_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }
};
