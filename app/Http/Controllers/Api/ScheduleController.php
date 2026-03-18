<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScheduleController extends Controller
{
    /**
     * GET /api/schedules
     * List all schedules (paginated).
     *
     * Query Params:
     * - ?available_only=1 → hanya jadwal yang masih tersedia
     * - ?sort=slots → urutkan berdasarkan slot tersedia (descending)
     * - ?per_page=10 → jumlah data per halaman (max 50)
     */
    public function index(): AnonymousResourceCollection
    {
        $request = request();

        $query = Schedule::withCount([
            'bookings as slots_taken' => fn ($q) =>
                $q->where('status', '!=', 'cancelled'),
        ]);

        // filter: cuman jadwal yang masih ada slot
        if ($request->boolean('available_only')) {
            $query->whereColumn('slot_capacity', '>', 'slots_taken');
        }

        // Sorting
        switch ($request->get('sort')) {
            case 'slots':
                $query->orderByRaw('(slot_capacity - slots_taken) DESC');
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        // pagination (limit max 50 biar aman)
        $perPage = min($request->get('per_page', 15), 50);

        $schedules = $query->paginate($perPage);

        return ScheduleResource::collection($schedules);
    }

    /**
     * GET /api/schedules/{id}
     * Show detail schedule + slot info.
     */
    public function show(Schedule $schedule): JsonResponse
    {
        $schedule->loadCount([
            'bookings as slots_taken' => fn ($q) =>
                $q->where('status', '!=', 'cancelled'),
        ]);

        return response()->json(new ScheduleResource($schedule));
    }
}