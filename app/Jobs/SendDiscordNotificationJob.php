<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\DiscordService;
use App\Services\ExcelExportService;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SendDiscordNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $sendTimeKey)
    {
    }

    public function handle(ExcelExportService $excel, DiscordService $discord): void
    {
        $targetDate = now()->addDay()->toDateString();
        $bookings = $this->queryBookingsForDate($targetDate);

        if ($bookings->isEmpty()) {
            return;
        }

        $filePath = $excel->generate($bookings, $targetDate);
        $discord->send("\u{200B}", $filePath, $this->buildSummaryEmbedsForDate($targetDate, $bookings));

        File::delete($filePath);
    }

    private function queryBookingsForDate(string $targetDate): Collection
    {
        return Booking::query()
            ->with(['lot.zone.location', 'timeSlot', 'facility'])
            ->join('time_slots', 'time_slots.id', '=', 'bookings.time_slot_id')
            ->where('bookings.status', 'confirmed')
            ->whereDate('bookings.booking_date', $targetDate)
            ->select('bookings.*')
            ->orderBy('bookings.booking_date')
            ->orderBy('time_slots.start_time')
            ->get();
    }

    private function buildSummaryEmbedsForDate(string $targetDate, Collection $bookings): array
    {
        $dateLabel = \Carbon\Carbon::parse($targetDate)->locale('id')->translatedFormat('d F Y');

        $totalBooking = $bookings->count();

        $totTent = 0;
        $totChair = 0;
        $totBarrel = 0;
        $totTable = 0;
        $totLamp = 0;

        foreach ($bookings as $b) {
            $f = $b->facility;
            $totTent += (int) ($f?->tent_count ?? 0);
            $totChair += (int) ($f?->chair_count ?? 0);
            $totBarrel += (int) ($f?->burn_barrel_count ?? 0);
            $totTable += ($f?->prayer_table ?? false) ? 1 : 0;
            $totLamp += ($f?->lamp ?? false) ? 1 : 0;
        }

        $desc = "📅 **Tanggal Ziarah:** {$dateLabel}\n\n"
            ."📊 **Ringkasan:**\n"
            ."- Total Booking: {$totalBooking}\n"
            ."- Total Tenda: {$totTent}\n"
            ."- Total Kursi: {$totChair}\n"
            ."- Total Tong Bakar: {$totBarrel}\n"
            ."- Meja Sembayang: {$totTable} booking\n"
            ."- Lampu: {$totLamp} booking\n\n"
            ."📎 Detail lengkap terlampir.";

        return [[
            'title' => 'Laporan Booking Ziarah (Besok)',
            'description' => $desc,
            'color' => 0x065F46,
        ]];
    }
}
