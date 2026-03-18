<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * POST /api/bookings
     * Buat booking baru untuk user yang sedang login
     */
    public function store(StoreBookingRequest $request)
    {
        $user = Auth::user();

        $booking = $this->bookingService->book(
            user: $user,
            scheduleId: $request->validated('schedule_id'),
        );

        // load relasi schedule + hitung ulang slot yang terpakai
        $booking->load([
            'schedule' => function ($query) {
                $query->withCount([
                    'bookings as slots_taken' => function ($q) {
                        $q->where('status', '!=', 'cancelled');
                    }
                ]);
            }
        ]);

        return (new BookingResource($booking))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * GET /api/bookings/me
     * Ambil semua booking milik user
     */
    public function myBookings(): AnonymousResourceCollection
    {
        $userId = Auth::id();

        $bookings = Booking::with([
                'schedule' => function ($query) {
                    $query->withCount([
                        'bookings as slots_taken' => function ($q) {
                            $q->where('status', '!=', 'cancelled');
                        }
                    ]);
                }
            ])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(15);

        return BookingResource::collection($bookings);
    }

    /**
     * DELETE /api/bookings/{id}
     * Cancel booking milik user
     */
    public function destroy(int $id)
    {
        $user = Auth::user();

        $booking = $this->bookingService->cancel($user, $id);

        // reload schedule biar slot langsung keupdate
        $booking->load([
            'schedule' => function ($query) {
                $query->withCount([
                    'bookings as slots_taken' => function ($q) {
                        $q->where('status', '!=', 'cancelled');
                    }
                ]);
            }
        ]);

        return new BookingResource($booking);
    }
}