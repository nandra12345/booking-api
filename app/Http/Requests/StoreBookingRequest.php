<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // route sudah pakai auth middleware,
        // jadi user yang sudah login boleh akses
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'schedule_id.exists' => 'jadwal yang dipilih tidak ditemukan',
        ];
    }
}