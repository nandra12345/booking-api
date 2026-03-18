<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * ubah data schedule jadi array untuk response api
     */
    public function toArray(Request $request): array
    {
        // ambil jumlah slot yang sudah terpakai (default 0 kalau belum ada)
        $slotsTaken = $this->slots_taken ?? 0;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'slot_capacity' => $this->slot_capacity,

            // info tambahan buat frontend
            'slots_taken' => $slotsTaken,
            'available_slots' => max(0, $this->slot_capacity - $slotsTaken),

            'created_at' => $this->created_at,
        ];
    }
}