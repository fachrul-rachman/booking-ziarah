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

        $import->status = 'running';
        $import->started_at = now();
        $import->total_rows = $importService->totalRowsFromPath($import->path);
        $import->processed_rows = 0;
        $import->errors_count = 0;
        $import->created_lots = 0;
        $import->errors = [];
        $import->save();

        try {
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
