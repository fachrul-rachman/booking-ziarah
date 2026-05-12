<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingsExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Kode Booking',
            'Tanggal Ziarah',
            'Jam',
            'Nama',
            'Email',
            'No. HP',
            'Lokasi',
            'Zona',
            'No. Lot',
            'Tenda',
            'Kursi',
            'Tong Bakar',
            'Meja Sembayang',
            'Lampu',
            'Status',
        ];
    }
}

