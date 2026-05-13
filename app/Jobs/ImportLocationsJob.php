<?php

namespace App\Jobs;

use App\Models\LocationImport;
use App\Services\LocationImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportLocationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $importId)
    {
    }

    public function handle(LocationImportService $importService): void
    {
        $import = LocationImport::query()->find($this->importId);
        if (!$import) {
            return;
        }

        try {
            // Mark running ASAP so UI doesn't get stuck "queued/menghitung" when anything fails early.
            LocationImport::query()
                ->whereKey($import->id)
                ->update([
                    'status' => 'running',
                    'started_at' => now(),
                    'processed_rows' => 0,
                    'errors_count' => 0,
                    'created_lots' => 0,
                    'errors' => [],
                    'updated_at' => now(),
                ]);

            $totalRows = $importService->totalRowsFromPath($import->path);
            LocationImport::query()
                ->whereKey($import->id)
                ->update([
                    'total_rows' => $totalRows,
                    'updated_at' => now(),
                ]);

            $importService->importFromPathWithProgress(
                $import->path,
                function (int $processed, int $total) use ($import) {
                    LocationImport::query()
                        ->whereKey($import->id)
                        ->update([
                            'processed_rows' => $processed,
                            'total_rows' => $total,
                            'updated_at' => now(),
                        ]);
                },
                function (int $createdLots, int $errorsCount, array $errorsSample) use ($import) {
                    LocationImport::query()
                        ->whereKey($import->id)
                        ->update([
                            'status' => 'completed',
                            'finished_at' => now(),
                            'created_lots' => $createdLots,
                            'errors_count' => $errorsCount,
                            'errors' => $errorsSample,
                            'updated_at' => now(),
                        ]);
                },
            );
        } catch (\Throwable $e) {
            report($e);

            LocationImport::query()
                ->whereKey($import->id)
                ->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'errors' => [['row' => null, 'message' => $e->getMessage()]],
                    'errors_count' => 1,
                    'updated_at' => now(),
                ]);
        }
    }
}
