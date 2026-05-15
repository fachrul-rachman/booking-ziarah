<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelExportService
{
    public function generate(Collection $bookings, ?string $targetDate = null): string
    {
        return $this->generateNamed($bookings, $targetDate, null);
    }

    public function generateNamed(Collection $bookings, ?string $targetDate, ?string $outputFilename): string
    {
        $templatePath = resource_path('excel/templates/contoh_discord_export_ziarah.xlsx');
        if (!File::exists($templatePath)) {
            throw new \RuntimeException("Template Excel tidak ditemukan: {$templatePath}");
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $date = $targetDate ? \Carbon\Carbon::parse($targetDate) : now();
        $sheet->setCellValue('A2', 'TANGGAL: '.$date->locale('id')->translatedFormat('d F Y'));

        $dataStartRow = 5;
        $totalsRow = $this->findTotalsRow($sheet) ?? 14;

        $count = $bookings->count();
        $availableRows = max(0, $totalsRow - $dataStartRow);

        if ($count > $availableRows) {
            $insert = $count - $availableRows;
            $sheet->insertNewRowBefore($totalsRow, $insert);
            $totalsRow += $insert;
        }

        $row = $dataStartRow;
        $no = 1;
        foreach ($bookings as $booking) {
            $timeSlot = $booking->timeSlot;
            $lot = $booking->lot;
            $zone = $lot?->zone;
            $location = $zone?->location;
            $facility = $booking->facility;

            $start = $timeSlot ? substr((string) $timeSlot->start_time, 0, 5) : '';
            $end = $timeSlot ? substr((string) $timeSlot->end_time, 0, 5) : '';
            $jam = trim($start.($end !== '' ? " - {$end}" : ''));

            $activityLabel = match ((string) ($booking->activity_type ?? '')) {
                'ziarah' => 'Ziarah',
                'naik_batu' => 'Naik Batu',
                'start_work' => 'Start Work',
                'wang_san' => 'Wang San',
                default => (string) ($booking->activity_type ?? ''),
            };

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", (string) $booking->booking_code);
            $sheet->setCellValue("C{$row}", $activityLabel);
            $sheet->setCellValue("D{$row}", $jam);
            $sheet->setCellValue("E{$row}", (string) ($location?->name ?? ''));
            $sheet->setCellValue("F{$row}", (string) $booking->name);
            $sheet->setCellValue("G{$row}", (string) ($booking->relationship ?? ''));
            $sheet->setCellValue("H{$row}", (string) $booking->phone);
            $sheet->setCellValue("I{$row}", (string) ($zone?->name ?? ''));
            $sheet->setCellValue("J{$row}", (string) ($lot?->number ?? ''));

            $sheet->setCellValue("K{$row}", (int) ($facility?->tent_count ?? 0));
            $sheet->setCellValue("L{$row}", (int) ($facility?->chair_count ?? 0));
            $sheet->setCellValue("M{$row}", (int) ($facility?->burn_barrel_count ?? 0));
            $sheet->setCellValue("N{$row}", ($facility?->prayer_table ?? false) ? 'Ya' : 'Tidak');
            $sheet->setCellValue("O{$row}", ($facility?->lamp ?? false) ? 'Ya' : 'Tidak');

            $row++;
            $no++;
        }

        $lastDataRow = $dataStartRow + max(0, $count - 1);
        if ($count === 0) {
            $sheet->setCellValue("K{$totalsRow}", 0);
            $sheet->setCellValue("L{$totalsRow}", 0);
            $sheet->setCellValue("M{$totalsRow}", 0);
            $sheet->setCellValue("N{$totalsRow}", 0);
            $sheet->setCellValue("O{$totalsRow}", 0);
        } else {
            $sheet->setCellValue("K{$totalsRow}", "=SUM(K{$dataStartRow}:K{$lastDataRow})");
            $sheet->setCellValue("L{$totalsRow}", "=SUM(L{$dataStartRow}:L{$lastDataRow})");
            $sheet->setCellValue("M{$totalsRow}", "=SUM(M{$dataStartRow}:M{$lastDataRow})");
            $sheet->setCellValue("N{$totalsRow}", "=COUNTIF(N{$dataStartRow}:N{$lastDataRow},\"Ya\")");
            $sheet->setCellValue("O{$totalsRow}", "=COUNTIF(O{$dataStartRow}:O{$lastDataRow},\"Ya\")");
        }

        $dir = storage_path('app/tmp/discord_exports');
        File::ensureDirectoryExists($dir);

        $fileName = $outputFilename
            ? Str::finish($outputFilename, '.xlsx')
            : ('booking_ziarah_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.xlsx');

        $filePath = $dir.DIRECTORY_SEPARATOR.$fileName;
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        return $filePath;
    }

    private function findTotalsRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): ?int
    {
        $cols = range('A', 'Z');
        for ($r = 1; $r <= 400; $r++) {
            foreach ($cols as $col) {
                $v = (string) ($sheet->getCell("{$col}{$r}")->getValue() ?? '');
                if ($v !== '' && Str::contains($v, 'Total Kebutuhan')) {
                    return $r;
                }
            }
        }

        return null;
    }
}
