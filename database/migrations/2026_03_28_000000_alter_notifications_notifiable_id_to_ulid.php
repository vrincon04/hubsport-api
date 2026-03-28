<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bases ya migradas con morphs() tenían notifiable_id BIGINT; User usa ULID (string).
     * Instalaciones nuevas ya crean la tabla con ulidMorphs y esta migración no hace nada.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $col = collect(Schema::getColumns('notifications'))->firstWhere('name', 'notifiable_id');
        if (! $col) {
            return;
        }

        $typeName = strtolower((string) ($col['type_name'] ?? $col['type'] ?? ''));
        if (in_array($typeName, ['varchar', 'char', 'string'], true)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' && $typeName === 'bigint') {
            foreach (Schema::getIndexes('notifications') as $index) {
                $cols = $index['columns'] ?? [];
                if (count($cols) === 2 && in_array('notifiable_id', $cols, true) && in_array('notifiable_type', $cols, true)) {
                    Schema::table('notifications', function (Blueprint $table) use ($index) {
                        $table->dropIndex($index['name']);
                    });
                    break;
                }
            }

            DB::statement('ALTER TABLE notifications MODIFY notifiable_id VARCHAR(26) NOT NULL');

            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_type', 'notifiable_id']);
            });

            return;
        }

        if ($driver === 'sqlite' && $typeName === 'integer') {
            Schema::rename('notifications', 'notifications_old');
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->ulidMorphs('notifiable');
                $table->json('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });

            DB::table('notifications_old')->orderBy('id')->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('notifications')->insert([
                        'id' => $row->id,
                        'type' => $row->type,
                        'notifiable_type' => $row->notifiable_type,
                        'notifiable_id' => (string) $row->notifiable_id,
                        'data' => $row->data,
                        'read_at' => $row->read_at,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            });

            Schema::drop('notifications_old');
        }
    }

    public function down(): void
    {
        //
    }
};
