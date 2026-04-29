<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\WebCancelDeadlineService;
use Illuminate\Http\Request;

class ReserveCancelController extends Controller
{
    public function show($token)
    {
        $reservation = Reservation::with('company')
            ->where('cancel_token', $token)
            ->firstOrFail();

        $company = $reservation->company;
        $deadlineService = app(WebCancelDeadlineService::class);
        $cancelDescription = $deadlineService->descriptionFor($reservation);
        $deadline = $deadlineService->deadlineFor($reservation);
        $isAlreadyCancelled = in_array($reservation->status, [Reservation::STATUS_CANCELLED, 'canceled'], true);
        $canCancel = ! $isAlreadyCancelled
            && now()->lte($deadline)
            && $reservation->status === Reservation::STATUS_RESERVED;

        return view('reserve.cancel', compact(
            'reservation',
            'company',
            'cancelDescription',
            'deadline',
            'canCancel',
            'isAlreadyCancelled'
        ));
    }

    public function cancel(Request $request, $token)
    {
        $reservation = Reservation::with('company')
            ->where('cancel_token', $token)
            ->firstOrFail();

        $deadlineService = app(WebCancelDeadlineService::class);
        $cancelDescription = $deadlineService->descriptionFor($reservation);
        $deadline = $deadlineService->deadlineFor($reservation);

        if ($reservation->status !== Reservation::STATUS_RESERVED) {
            return redirect()
                ->back()
                ->with('error', 'この予約はすでにキャンセル済み、またはキャンセルできません。');
        }

        if (now()->gt($deadline)) {
            return redirect()
                ->back()
                ->with('error', "Webでのキャンセルは{$cancelDescription}です。それ以降はお電話でご連絡ください。");
        }

        $reservation->update([
            'status' => Reservation::STATUS_CANCELLED,
        ]);

        return redirect()
            ->route('reserve.cancel.complete', ['token' => $reservation->cancel_token])
            ->with('success', 'ご予約をキャンセルしました。');
    }

    public function complete($token)
    {
        $reservation = Reservation::with('company')
            ->where('cancel_token', $token)
            ->firstOrFail();

        return view('reserve.cancel_complete', [
            'reservation' => $reservation,
            'company' => $reservation->company,
        ]);
    }
}
