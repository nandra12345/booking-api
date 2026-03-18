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
    // -------------------------------------------------------------------------
    // public api
    // -------------------------------------------------------------------------

    /**
     * buat booking untuk user pada schedule
     *
     * @throws ConflictHttpException
     * @throws UnprocessableEntityHttpException
     */
    public function book(User $user, int $scheduleId): Booking
    {
        return DB::transaction(function () use ($user, $scheduleId) {
            /** @var Schedule $schedule */
            $schedule = Schedule::lockForUpdate()->findOrFail($scheduleId);

            $this->ensureNotAlreadyBooked($user, $schedule);
            $this->ensureSlotAvailable($schedule);

            return Booking::create([
                'schedule_id' => $schedule->id,
                'user_id'     => $user->id,
                'status'      => 'booked',
            ]);
        });
    }

    /**
     * batalkan booking milik user
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function cancel(User $user, int $bookingId): Booking
    {
        /** @var Booking $booking */
        $booking = Booking::findOrFail($bookingId);

        if ($booking->user_id !== $user->id) {
            abort(403, 'you are not allowed to cancel this booking.');
        }

        if ($booking->isCancelled()) {
            throw new UnprocessableEntityHttpException('this booking is already cancelled.');
        }

        $booking->cancel();

        return $booking->fresh()->load('schedule');
    }

    // -------------------------------------------------------------------------
    // private helpers
    // -------------------------------------------------------------------------

    private function ensureNotAlreadyBooked(User $user, Schedule $schedule): void
    {
        $alreadyBooked = Booking::where('schedule_id', $schedule->id)
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($alreadyBooked) {
            throw new ConflictHttpException(
                'you have already booked this schedule.'
            );
        }
    }

    private function ensureSlotAvailable(Schedule $schedule): void
    {
        if (! $schedule->hasAvailableSlots()) {
            throw new UnprocessableEntityHttpException(
                'no available slots remaining for this schedule.'
            );
        }
    }
}