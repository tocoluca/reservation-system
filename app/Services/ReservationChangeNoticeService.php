<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Reservation;
use App\Models\ReservationChangeNotice;
use App\Models\ReservationChangeNoticeItem;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationChangeNoticeService
{
    public function createForClosedDate(
        Company $company,
        string $date,
        ?string $reasonText = null
    ): ?ReservationChangeNotice {
        $targetDate = Carbon::parse($date)->toDateString();

        $reservations = Reservation::query()
            ->where('company_id', $company->id)
            ->whereDate('start_at', $targetDate)
            ->where('status', 'reserved')
            ->get();

        if ($reservations->isEmpty()) {
            return null;
        }

        $title = $targetDate . ' の休業による予約変更連絡';
        $reasonText = $reasonText ?: '店舗休業のため、ご予約内容の変更をお願いしております。';

        return $this->createNoticeWithReservations(
            company: $company,
            reservations: $reservations,
            title: $title,
            targetDate: $targetDate,
            reasonType: 'closed',
            reasonText: $reasonText
        );
    }

    public function createForTimeChange(
        Company $company,
        string $date,
        ?string $openTime,
        ?string $closeTime,
        ?string $reasonText = null
    ): ?ReservationChangeNotice {
        $targetDate = Carbon::parse($date)->toDateString();

        if (empty($openTime) || empty($closeTime)) {
            return null;
        }

        $openDateTime = Carbon::parse($targetDate . ' ' . $openTime);
        $closeDateTime = Carbon::parse($targetDate . ' ' . $closeTime);

        $reservations = Reservation::query()
            ->where('company_id', $company->id)
            ->whereDate('start_at', $targetDate)
            ->where('status', 'reserved')
            ->get()
            ->filter(function ($reservation) use ($openDateTime, $closeDateTime) {
                $start = Carbon::parse($reservation->start_at);
                $end = Carbon::parse($reservation->end_at);

                return $start->lt($openDateTime) || $end->gt($closeDateTime);
            })
            ->values();

        if ($reservations->isEmpty()) {
            return null;
        }

        $title = $targetDate . ' の営業時間変更による予約変更連絡';
        $reasonText = $reasonText ?: "営業時間変更（{$openTime}〜{$closeTime}）のため、ご予約内容の変更をお願いしております。";

        return $this->createNoticeWithReservations(
            company: $company,
            reservations: $reservations,
            title: $title,
            targetDate: $targetDate,
            reasonType: 'time_changed',
            reasonText: $reasonText
        );
    }

    public function createForStaffVacation(
        Company $company,
        Staff $staff,
        Carbon $startAt,
        Carbon $endAt,
        ?string $reasonText = null
    ): ?ReservationChangeNotice {
        $reservations = Reservation::query()
            ->where('company_id', $company->id)
            ->where('staff_id', $staff->id)
            ->where('status', 'reserved')
            ->where(function ($q) use ($startAt, $endAt) {
                $q->where('start_at', '<', $endAt)
                  ->where('end_at', '>', $startAt);
            })
            ->get();

        if ($reservations->isEmpty()) {
            return null;
        }

        $targetDate = $startAt->toDateString();
        $title = $targetDate . ' ' . $staff->name . ' 休暇による予約変更連絡';
        $reasonText = $reasonText ?: $staff->name . ' の休暇のため、ご予約内容の変更をお願いしております。';

        return $this->createNoticeWithReservations(
            company: $company,
            reservations: $reservations,
            title: $title,
            targetDate: $targetDate,
            reasonType: 'staff_off',
            reasonText: $reasonText
        );
    }

    public function createForStaffShiftOff(
        Company $company,
        Staff $staff,
        string $date,
        ?string $reasonText = null
    ): ?ReservationChangeNotice {
        $targetDate = Carbon::parse($date)->toDateString();

        $reservations = Reservation::query()
            ->where('company_id', $company->id)
            ->where('staff_id', $staff->id)
            ->whereDate('start_at', $targetDate)
            ->where('status', 'reserved')
            ->get();

        if ($reservations->isEmpty()) {
            return null;
        }

        $title = $targetDate . ' ' . $staff->name . ' シフト変更による予約変更連絡';
        $reasonText = $reasonText ?: $staff->name . ' のシフト変更のため、ご予約内容の変更をお願いしております。';

        return $this->createNoticeWithReservations(
            company: $company,
            reservations: $reservations,
            title: $title,
            targetDate: $targetDate,
            reasonType: 'staff_shift_off',
            reasonText: $reasonText
        );
    }

    public function createForStaffShiftTimeChange(
        Company $company,
        Staff $staff,
        string $date,
        string $startTime,
        string $endTime,
        ?string $reasonText = null
    ): ?ReservationChangeNotice {
        $targetDate = Carbon::parse($date)->toDateString();
        $shiftStart = Carbon::parse($targetDate . ' ' . $startTime);
        $shiftEnd = Carbon::parse($targetDate . ' ' . $endTime);

        $reservations = Reservation::query()
            ->where('company_id', $company->id)
            ->where('staff_id', $staff->id)
            ->whereDate('start_at', $targetDate)
            ->where('status', 'reserved')
            ->get()
            ->filter(function ($reservation) use ($shiftStart, $shiftEnd) {
                $start = Carbon::parse($reservation->start_at);
                $end = Carbon::parse($reservation->end_at);

                return $start->lt($shiftStart) || $end->gt($shiftEnd);
            })
            ->values();

        if ($reservations->isEmpty()) {
            return null;
        }

        $title = $targetDate . ' ' . $staff->name . ' シフト変更による予約変更連絡';
        $reasonText = $reasonText ?: $staff->name . ' のシフト変更のため、ご予約内容の変更をお願いしております。';

        return $this->createNoticeWithReservations(
            company: $company,
            reservations: $reservations,
            title: $title,
            targetDate: $targetDate,
            reasonType: 'staff_shift_changed',
            reasonText: $reasonText
        );
    }

    protected function createNoticeWithReservations(
        Company $company,
        Collection $reservations,
        string $title,
        string $targetDate,
        string $reasonType,
        string $reasonText
    ): ReservationChangeNotice {
        return DB::transaction(function () use ($company, $reservations, $title, $targetDate, $reasonType, $reasonText) {
            $notice = ReservationChangeNotice::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'title' => $title,
                    'target_date' => $targetDate,
                    'reason_type' => $reasonType,
                ],
                [
                    'reason_text' => $reasonText,
                    'status' => 'in_progress',
                    'created_by' => auth()->guard('company')->id(),
                ]
            );

            foreach ($reservations as $reservation) {
                ReservationChangeNoticeItem::updateOrCreate(
                    [
                        'notice_id' => $notice->id,
                        'reservation_id' => $reservation->id,
                    ],
                    [
                        'company_id' => $company->id,
                        'customer_id' => $reservation->customer_id ?? null,
                        'customer_name' => $reservation->customer_name,
                        'customer_email' => $reservation->customer_email,
                        'customer_phone' => $reservation->customer_phone,
                        'contact_type' => !empty($reservation->customer_email) ? 'mail' : 'phone',
                        'contact_status' => !empty($reservation->customer_email) ? 'pending' : 'phone_pending',
                        'response_status' => 'waiting',
                        'response_token' => !empty($reservation->customer_email)
                            ? ($this->resolveResponseToken($notice->id, $reservation->id))
                            : null,
                    ]
                );
            }

            return $notice;
        });
    }

    protected function resolveResponseToken(int $noticeId, int $reservationId): string
    {
        $existing = ReservationChangeNoticeItem::query()
            ->where('notice_id', $noticeId)
            ->where('reservation_id', $reservationId)
            ->value('response_token');

        return $existing ?: Str::random(64);
    }
}
