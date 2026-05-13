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
        foreach ($bookings as $booking) {
            $timeSlot = $booking->timeSlot;
            $lot = $booking->lot;
            $zone = $lot?->zone;
            $location = $zone?->location;
            $facility = $booking->facility;

            $start = $timeSlot ? substr((string) $timeSlot->start_time, 0, 5) : '';
            $end = $timeSlot ? substr((string) $timeSlot->end_time, 0, 5) : '';
            $jam = trim($start.($end !== '' ? " - {$end}" : ''));

            $sheet->setCellValue("A{$row}", (string) $booking->booking_code);
            $sheet->setCellValue("B{$row}", $jam);
            $sheet->setCellValue("C{$row}", (string) ($location?->name ?? ''));
            $sheet->setCellValue("D{$row}", (string) $booking->name);
            $sheet->setCellValue("E{$row}", (string) ($booking->relationship ?? ''));
            $sheet->setCellValue("F{$row}", (string) $booking->phone);
            $sheet->setCellValue("G{$row}", (string) ($zone?->name ?? ''));
            $sheet->setCellValue("H{$row}", (string) ($lot?->number ?? ''));

            $sheet->setCellValue("I{$row}", (int) ($facility?->tent_count ?? 0));
            $sheet->setCellValue("J{$row}", (int) ($facility?->chair_count ?? 0));
            $sheet->setCellValue("K{$row}", (int) ($facility?->burn_barrel_count ?? 0));
            $sheet->setCellValue("L{$row}", ($facility?->prayer_table ?? false) ? 'Ya' : 'Tidak');
            $sheet->setCellValue("M{$row}", ($facility?->lamp ?? false) ? 'Ya' : 'Tidak');

            $row++;
        }

        $lastDataRow = $dataStartRow + max(0, $count - 1);
        if ($count === 0) {
            $sheet->setCellValue("I{$totalsRow}", 0);
            $sheet->setCellValue("J{$totalsRow}", 0);
            $sheet->setCellValue("K{$totalsRow}", 0);
            $sheet->setCellValue("L{$totalsRow}", 0);
            $sheet->setCellValue("M{$totalsRow}", 0);
        } else {
            $sheet->setCellValue("I{$totalsRow}", "=SUM(I{$dataStartRow}:I{$lastDataRow})");
            $sheet->setCellValue("J{$totalsRow}", "=SUM(J{$dataStartRow}:J{$lastDataRow})");
            $sheet->setCellValue("K{$totalsRow}", "=SUM(K{$dataStartRow}:K{$lastDataRow})");
            $sheet->setCellValue("L{$totalsRow}", "=COUNTIF(L{$dataStartRow}:L{$lastDataRow},\"Ya\")");
            $sheet->setCellValue("M{$totalsRow}", "=COUNTIF(M{$dataStartRow}:M{$lastDataRow},\"Ya\")");
        }

        $dir = storage_path('app/tmp/discord_exports');
        File::ensureDirectoryExists($dir);

        $filePath = $dir.DIRECTORY_SEPARATOR.'booking_ziarah_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        return $filePath;
    }

    private function findTotalsRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): ?int
    {
        for ($r = 1; $r <= 300; $r++) {
            $v = (string) ($sheet->getCell("F{$r}")->getValue() ?? '');
            if ($v !== '' && Str::contains($v, 'Total Kebutuhan')) {
                return $r;
            }
        }

        return null;
    }
}
