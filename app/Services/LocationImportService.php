<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Lot;
use App\Models\Zone;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetReaderException;

class LocationImportService
{
    public function totalRowsFromPath(string $path): int
    {
        $fullPath = Storage::path($path);

        try {
            $reader = IOFactory::createReaderForFile($fullPath);
        } catch (SpreadsheetReaderException $e) {
            throw new \RuntimeException('Format file Excel tidak dikenali.');
        }

        $info = $reader->listWorksheetInfo($fullPath);
        $highestRow = (int) (($info[0]['totalRows'] ?? 0) ?: 0);

        return max(0, $highestRow - 1); // skip header row
    }

    public function previewFromPath(string $path): array
    {
        try {
            $result = $this->previewRowsChunked($path, 200);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'rows_count' => 0,
                'errors' => [['row' => null, 'message' => $e->getMessage()]],
                'sample' => [],
            ];
        }

        return $result;
    }

    public function importFromPath(string $path): array
    {
        return $this->importFromPathWithProgress($path, null, null);
    }

    /**
     * @param  null|callable(int $processedRows,int $totalRows):void  $onProgress
     * @param  null|callable(int $createdLots,int $errorsCount,array $errorsSample):void  $onDone
     */
    public function importFromPathWithProgress(string $path, ?callable $onProgress, ?callable $onDone): array
    {
        $errors = [];
        $createdLots = 0;
        $processedRows = 0;
        $totalRows = $this->totalRowsFromPath($path);

        $meta = $this->streamRowsChunked(
            $path,
            200,
            function (int $rowIndex, string $location, string $zone, string $lotNumber) use (&$createdLots, &$processedRows, &$errors) {
            if ($location === '' && $zone === '' && $lotNumber === '') {
                return;
            }

            if ($location === '' || $zone === '' || $lotNumber === '') {
                $errors[] = [
                    'row' => $rowIndex,
                    'message' => 'Kolom A/B/C wajib terisi (Lokasi/Zona/Nomor Lot).',
                ];
                return;
            }

            $processedRows++;

            $locationModel = Location::query()->firstOrCreate(['name' => $location], ['is_active' => true]);
            $zoneModel = Zone::query()->firstOrCreate(
                ['location_id' => $locationModel->id, 'name' => $zone],
                ['is_active' => true],
            );

            $lot = Lot::query()->firstOrCreate(
                ['zone_id' => $zoneModel->id, 'number' => $lotNumber],
                ['is_active' => true],
            );

            if ($lot->wasRecentlyCreated) {
                $createdLots++;
            }
        },
            $onProgress ? function (int $processedPhysical, int $totalPhysical) use ($onProgress) {
                $onProgress($processedPhysical, $totalPhysical);
            } : null,
        );

        Storage::delete($path);

        if ($onProgress) {
            $onProgress($totalRows, $totalRows);
        }

        if ($onDone) {
            $onDone($createdLots, count($errors), array_slice($errors, 0, 50));
        }

        return [
            'imported_rows' => $processedRows,
            'created_lots' => $createdLots,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{0: array<int,array{location:string,zone:string,lot_number:string}>, 1: array<int,array{row:int|null,message:string}>}
     */
    private function readRows(string $path): array
    {
        $fullPath = Storage::path($path);

        try {
            $reader = IOFactory::createReaderForFile($fullPath);
        } catch (SpreadsheetReaderException $e) {
            throw new \RuntimeException('Format file Excel tidak dikenali.');
        }

        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = (int) $sheet->getHighestRow();

        $rows = [];
        $errors = [];
        $hasAnyDataOutsideABC = false;

        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $location = trim((string) $sheet->getCell("A{$rowIndex}")->getValue());
            $zone = trim((string) $sheet->getCell("B{$rowIndex}")->getValue());
            $lotNumber = trim((string) $sheet->getCell("C{$rowIndex}")->getValue());

            for ($colOrd = ord('D'); $colOrd <= ord('Z'); $colOrd++) {
                $col = chr($colOrd);
                $val = trim((string) $sheet->getCell("{$col}{$rowIndex}")->getValue());
                if ($val !== '') {
                    $hasAnyDataOutsideABC = true;
                    break;
                }
            }

            if ($location === '' && $zone === '' && $lotNumber === '') {
                continue;
            }

            if ($location === '' || $zone === '' || $lotNumber === '') {
                $errors[] = [
                    'row' => $rowIndex,
                    'message' => 'Kolom A/B/C wajib terisi (Lokasi/Zona/Nomor Lot).',
                ];
                continue;
            }

            $rows[] = [
                'location' => $location,
                'zone' => $zone,
                'lot_number' => $lotNumber,
            ];
        }

        if (count($rows) === 0 && count($errors) === 0 && ($highestRow > 1 || $hasAnyDataOutsideABC)) {
            $errors[] = [
                'row' => null,
                'message' => 'Format kolom salah: pastikan Kolom A = Lokasi, Kolom B = Zona, Kolom C = Nomor Lot.',
            ];
        }

        return [$rows, $errors];
    }

    private function previewRowsChunked(string $path, int $chunkSize): array
    {
        $rowsCount = 0;
        $errors = [];
        $sample = [];
        $hasAnyDataOutsideABC = false;
        $seenAnyRow = false;

        $meta = $this->streamRowsChunked($path, $chunkSize, function (int $rowIndex, string $location, string $zone, string $lotNumber, bool $outsideABC) use (&$rowsCount, &$errors, &$sample, &$hasAnyDataOutsideABC, &$seenAnyRow) {
            if ($outsideABC) {
                $hasAnyDataOutsideABC = true;
            }

            if ($location === '' && $zone === '' && $lotNumber === '') {
                return;
            }

            $seenAnyRow = true;

            if ($location === '' || $zone === '' || $lotNumber === '') {
                $errors[] = [
                    'row' => $rowIndex,
                    'message' => 'Kolom A/B/C wajib terisi (Lokasi/Zona/Nomor Lot).',
                ];
                return;
            }

            $rowsCount++;

            if (count($sample) < 25) {
                $sample[] = [
                    'location' => $location,
                    'zone' => $zone,
                    'lot_number' => $lotNumber,
                ];
            }
        });

        if ($rowsCount === 0 && count($errors) === 0 && ($meta['highestRow'] > 1 || $hasAnyDataOutsideABC || $seenAnyRow)) {
            $errors[] = [
                'row' => null,
                'message' => 'Format kolom salah: pastikan Kolom A = Lokasi, Kolom B = Zona, Kolom C = Nomor Lot.',
            ];
        }

        return [
            'ok' => count($errors) === 0,
            'rows_count' => $rowsCount,
            'errors' => $errors,
            'sample' => $sample,
        ];
    }

    /**
     * Stream rows from Excel without loading whole sheet.
     *
     * Callback signatures:
     * - (int $rowIndex, string $location, string $zone, string $lotNumber, bool $outsideABC) for preview
     * - (int $rowIndex, string $location, string $zone, string $lotNumber) for import
     *
     * @return array{highestRow:int}
     */
    private function streamRowsChunked(string $path, int $chunkSize, callable $callback, ?callable $afterChunk = null): array
    {
        $fullPath = Storage::path($path);

        try {
            $reader = IOFactory::createReaderForFile($fullPath);
        } catch (SpreadsheetReaderException $e) {
            throw new \RuntimeException('Format file Excel tidak dikenali.');
        }

        $reader->setReadDataOnly(true);

        $info = $reader->listWorksheetInfo($fullPath);
        $highestRow = (int) (($info[0]['totalRows'] ?? 0) ?: 0);
        if ($highestRow < 2) {
            return ['highestRow' => $highestRow];
        }

        $callbackWantsOutsideABC = (new \ReflectionFunction(\Closure::fromCallable($callback)))->getNumberOfParameters() >= 5;

        $processedPhysicalRows = 0;

        for ($startRow = 2; $startRow <= $highestRow; $startRow += $chunkSize) {
            $endRow = min($highestRow, $startRow + $chunkSize - 1);

            $reader->setReadFilter(new class($startRow, $endRow) implements IReadFilter {
                public function __construct(private readonly int $startRow, private readonly int $endRow)
                {
                }

                public function readCell($columnAddress, $row, $worksheetName = ''): bool
                {
                    if ($row === 1) {
                        return true;
                    }

                    return $row >= $this->startRow && $row <= $this->endRow;
                }
            });

            $spreadsheet = $reader->load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();

            for ($rowIndex = $startRow; $rowIndex <= $endRow; $rowIndex++) {
                $location = trim((string) $sheet->getCell("A{$rowIndex}")->getValue());
                $zone = trim((string) $sheet->getCell("B{$rowIndex}")->getValue());
                $lotNumber = trim((string) $sheet->getCell("C{$rowIndex}")->getValue());

                $outsideABC = false;
                for ($colOrd = ord('D'); $colOrd <= ord('Z'); $colOrd++) {
                    $col = chr($colOrd);
                    $val = trim((string) $sheet->getCell("{$col}{$rowIndex}")->getValue());
                    if ($val !== '') {
                        $outsideABC = true;
                        break;
                    }
                }

                if ($callbackWantsOutsideABC) {
                    $callback($rowIndex, $location, $zone, $lotNumber, $outsideABC);
                } else {
                    $callback($rowIndex, $location, $zone, $lotNumber);
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            if ($afterChunk) {
                $processedPhysicalRows = max($processedPhysicalRows, $endRow - 1); // minus header row
                $afterChunk($processedPhysicalRows, max(0, $highestRow - 1));
            }
        }

        return ['highestRow' => $highestRow];
    }
}
