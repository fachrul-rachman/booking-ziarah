<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minDate = now()->addDays(2)->toDateString();

        return [
            'activity_type' => ['required', 'string', 'in:ziarah,naik_batu,start_work,wang_san'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
            'lot_id' => ['required', 'integer', 'exists:lots,id'],
            'booking_date' => ['required', 'date', "after_or_equal:{$minDate}"],
            'time_slot_id' => ['required', 'integer', 'exists:time_slots,id'],

            'tent_count' => ['required', 'integer', 'min:0', 'max:1'],
            'chair_count' => ['required', 'integer', 'min:5', 'max:10'],
            'burn_barrel_count' => ['required', 'integer', 'min:0', 'max:2'],
            'prayer_table' => ['required', 'boolean'],
            'lamp' => ['required', 'boolean'],

            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'in:Suami,Istri,Anak,Kakak,Adik,Ipar,Mertua,Menantu,Paman,Bibi'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }
}
