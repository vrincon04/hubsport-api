<?php

namespace App\Console\Commands;

use Database\Seeders\DemoFullShowcaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResetDemoDataset extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Restaura el dataset aislado del usuario demo';

    public function handle(DemoFullShowcaseSeeder $seeder): int
    {
        $lock = Cache::lock('demo-dataset-reset', 300);

        try {
            $lock->block(5);
            Log::info('Demo dataset reset started');

            DB::transaction(fn () => $seeder->run(), 3);

            Log::info('Demo dataset reset completed');
            $this->info('Dataset demo restaurado correctamente.');

            return self::SUCCESS;
        } catch (LockTimeoutException) {
            Log::warning('Demo dataset reset skipped because another reset is running');
            $this->warn('Ya hay una restauración demo en curso.');

            return self::FAILURE;
        } catch (Throwable $exception) {
            Log::error('Demo dataset reset failed', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            $this->error('No se pudo restaurar el dataset demo.');

            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
