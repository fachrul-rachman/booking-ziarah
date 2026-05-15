<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\DiscordService;
use App\Services\ExcelExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

        $attachments = $this->generateAttachmentsPerLocation($excel, $targetDate, $bookings);
        if (empty($attachments)) {
            return;
        }

        $message = $this->buildCombinedSummaryMessageForDate($targetDate, $bookings);
        $discord->sendWithAttachments($message, $attachments);

        foreach ($attachments as $att) {
            if (!empty($att['path']) && is_string($att['path'])) {
                File::delete($att['path']);
            }
        }
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

    private function buildCombinedSummaryMessageForDate(string $targetDate, Collection $bookings): string
    {
        $dateLabel = \Carbon\Carbon::parse($targetDate)->locale('id')->translatedFormat('d F Y');

        $activityMap = [
            'ziarah' => 'Ziarah',
            'naik_batu' => 'Naik Batu',
            'start_work' => 'Start Work',
            'wang_san' => 'Wang San',
        ];

        $blocks = [];
        foreach ($activityMap as $key => $label) {
            $subset = $bookings->filter(fn ($b) => (string) ($b->activity_type ?? 'ziarah') === $key);
            if ($subset->isEmpty()) {
                continue;
            }

            [$totBooking, $totTent, $totChair, $totBarrel, $totTable, $totLamp] = $this->summarizeFacilities($subset);

            $blocks[] =
                "Laporan Booking {$label}\n".
                "📅 Tanggal {$label}: {$dateLabel}\n\n".
                "📊 Ringkasan:\n".
                "Total Booking: {$totBooking}\n".
                "Total Tenda: {$totTent}\n".
                "Total Kursi: {$totChair}\n".
                "Total Tong Bakar: {$totBarrel}\n".
                "Meja Sembayang: {$totTable} booking\n".
                "Lampu: {$totLamp} booking\n\n".
                "📎 Detail lengkap terlampir.";
        }

        return implode("\n\n", $blocks);
    }

    /**
     * @return array<int,array{path:string,filename:string}>
     */
    private function generateAttachmentsPerLocation(ExcelExportService $excel, string $targetDate, Collection $bookings): array
    {
        $dateStamp = \Carbon\Carbon::parse($targetDate)->format('d-m-Y');

        $byLocation = $bookings->groupBy(function ($b) {
            return (string) ($b->lot?->zone?->location?->name ?? 'Tanpa Lokasi');
        });

        $attachments = [];

        foreach ($byLocation as $locationName => $locationBookings) {
            $slug = Str::slug($locationName, '_');
            if ($slug === '') {
                $slug = 'lokasi';
            }

            $ziarah = $locationBookings->filter(fn ($b) => (string) ($b->activity_type ?? 'ziarah') === 'ziarah');
            if ($ziarah->isNotEmpty()) {
                $filename = "ziarah_{$slug}_{$dateStamp}.xlsx";
                $attachments[] = [
                    'path' => $excel->generateNamed($ziarah->values(), $targetDate, $filename),
                    'filename' => $filename,
                ];
            }

            $others = $locationBookings->filter(fn ($b) => in_array((string) ($b->activity_type ?? 'ziarah'), ['naik_batu', 'start_work', 'wang_san'], true));
            if ($others->isNotEmpty()) {
                $filename = "kegiatan_{$slug}_{$dateStamp}.xlsx";
                $attachments[] = [
                    'path' => $excel->generateNamed($others->values(), $targetDate, $filename),
                    'filename' => $filename,
                ];
            }
        }

        return $attachments;
    }

    /**
     * @return array{0:int,1:int,2:int,3:int,4:int,5:int}
     */
    private function summarizeFacilities(Collection $bookings): array
    {
        $totBooking = $bookings->count();

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

        return [$totBooking, $totTent, $totChair, $totBarrel, $totTable, $totLamp];
    }
}

