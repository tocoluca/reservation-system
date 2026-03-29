<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReserveCancelController extends Controller
{
    public function show($token)
    {
        $reservation = Reservation::with('company')
            ->where('cancel_token', $token)
            ->firstOrFail();

        $company = $reservation->company;
        $cancelHours = (int) ($company->web_cancel_deadline_hours ?? 24);
        $deadline = Carbon::parse($reservation->start_at)->subHours($cancelHours);
        $canCancel = now()->lte($deadline) && $reservation->status === 'reserved';

        return view('reserve.cancel', compact(
            'reservation',
            'company',
            'cancelHours',
            'deadline',
            'canCancel'
        ));
    }

    public function cancel(Request $request, $token)
    {
        $reservation = Reservation::with('company')
            ->where('cancel_token', $token)
            ->firstOrFail();

        $company = $reservation->company;
        $cancelHours = (int) ($company->web_cancel_deadline_hours ?? 24);
        $deadline = Carbon::parse($reservation->start_at)->subHours($cancelHours);

        if ($reservation->status !== 'reserved') {
            return redirect()
                ->back()
                ->with('error', 'この予約はすでにキャンセル済み、またはキャンセルできません。');
        }

        if (now()->gt($deadline)) {
            return redirect()
                ->back()
                ->with('error', "Webでのキャンセルは予約時間の{$cancelHours}時間前までです。それ以降はお電話でご連絡ください。");
        }

        $reservation->update([
            'status' => 'cancelled',
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