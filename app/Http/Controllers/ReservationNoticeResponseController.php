<?php

namespace App\Http\Controllers;

use App\Models\ReservationChangeNoticeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationNoticeResponseController extends Controller
{
    public function show(string $token)
    {
        $item = ReservationChangeNoticeItem::with(['notice', 'reservation'])
            ->where('response_token', $token)
            ->firstOrFail();

        if (is_null($item->mail_opened_at)) {
            $item->update([
                'mail_opened_at' => now(),
            ]);
        }

        return view('reserve.notice_response', compact('item'));
    }

    public function confirm(Request $request, string $token)
    {
        $item = ReservationChangeNoticeItem::with(['notice', 'reservation'])
            ->where('response_token', $token)
            ->firstOrFail();

        if ($item->confirmed_at) {
            return view('reserve.notice_response_done', compact('item'));
        }

        DB::transaction(function () use ($item) {
            $item->update([
                'response_status' => 'confirmed',
                'contact_status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            $reservation = $item->reservation;

            if ($reservation && !in_array($reservation->status, ['cancelled', 'completed'], true)) {
                $reservation->update([
                    'status' => 'cancelled',
                    'cancelled_type' => 'shop',
                    'cancelled_at' => now(),
                    'cancelled_reason' => $item->notice->reason_text ?? '店舗都合による予約変更',
                ]);
            }

            $item->update([
                'contact_status' => 'closed',
                'response_status' => 'closed',
                'cancel_reason_type' => 'shop',
                'cancelled_at' => now(),
            ]);
        });

        return view('reserve.notice_response_done', compact('item'));
    }
}