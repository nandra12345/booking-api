<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BookingService
{
   //public api
    /**
     * Create a new booking for $user on $schedule.
     *
     * Business rules enforced:
     *   1. A user cannot book the same schedule more than once.
     *   2. Slot capacity must not be exceeded.
     *
     * @throws ConflictHttpException
     * @throws UnprocessableEntityHttpException
     */
   
    public function book(User $user, int $scheduleId): Booking
    {
        return DB::transaction(function () use ($user, $scheduleId) {
            
            /** @var Schedule $schedule */
            $schedule = Schedule::lockForUpdate()->findOrFail($scheduleId);

            $this->ensureSlotAvailable($schedule);

            
            $existing = Booking::withTrashed()
                ->where('schedule_id', $schedule->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                
                if (! $existing->trashed()) {
                    throw new ConflictHttpException(
                        'You have already booked this schedule.'
                    );
                }

                
                $existing->restore();
                $existing->update(['status' => 'confirmed']); // FIX #2

                return $existing->fresh();
            }

            // No prior booking → create fresh row
            return Booking::create([
                'schedule_id' => $schedule->id,
                'user_id'     => $user->id,
                'status'      => 'confirmed', // FIX #2
            ]);
        });
    }

       public function cancel(User $user, int $bookingId): Booking
    {
        /** @var Booking $booking */
        $booking = Booking::findOrFail($bookingId);

        if ($booking->user_id !== $user->id) {
            abort(403, 'You are not allowed to cancel this booking.');
        }

        if ($booking->isCancelled()) {
            throw new UnprocessableEntityHttpException('This booking is already cancelled.');
        }

        
        $booking->cancel();

        return $booking->fresh(['schedule']);
    }


    private function ensureSlotAvailable(Schedule $schedule): void
    {
        if (! $schedule->hasAvailableSlots()) {
            throw new UnprocessableEntityHttpException(
                'No available slots remaining for this schedule.'
            );
        }
    }
}