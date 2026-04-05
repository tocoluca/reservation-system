<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Mail\ReservationChangeNoticeMail;
use App\Models\Reservation;
use App\Models\ReservationChangeNotice;
use App\Models\ReservationChangeNoticeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReservationChangeNoticeController extends Controller
{
    public function index()
    {
        $company = auth()->guard('company')->user()->company;

        $notices = ReservationChangeNotice::query()
            ->where('company_id', $company->id)
            ->withCount('items')
            ->withCount([
                'items as pending_count' => function ($q) {
                    $q->whereIn('response_status', ['waiting', 'mail_sent', 'no_response']);
                },
                'items as confirmed_count' => function ($q) {
                    $q->whereIn('response_status', ['confirmed', 'phone_confirmed', 'closed']);
                },
            ])
            ->latest()
            ->paginate(20);

        return view('company.reservation_change_notices.index', compact('notices'));
    }

    public function show(ReservationChangeNotice $notice)
    {
        $company = auth()->guard('company')->user()->company;
        abort_unless((int) $notice->company_id === (int) $company->id, 403);

        $notice->load(['items.reservation']);

        return view('company.reservation_change_notices.show', compact('notice'));
    }

    public function createFromClosedDate(Request $request)
    {
        $companyUser = auth()->guard('company')->user();
        $company = $companyUser->company;

        $validated = $request->validate([
            'target_date' => ['required', 'date'],
            'reason_type' => ['nullable', 'string', 'max:50'],
            'reason_text' => ['required', 'string', 'max:2000'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $targetDate = $validated['target_date'];

        $reservations = Reservation::query()
            ->where('company_id', $company->id)
            ->whereDate('start_at', $targetDate)
            ->where('status', 'reserved')
            ->get();

        if ($reservations->isEmpty()) {
            return back()->with('error', '対象となる予約がありません。');
        }

        DB::transaction(function () use ($validated, $company, $companyUser, $reservations, $targetDate) {
            $notice = ReservationChangeNotice::create([
                'company_id' => $company->id,
                'title' => $validated['title'] ?: ($targetDate . ' の営業日変更による予約変更連絡'),
                'target_date' => $targetDate,
                'reason_type' => $validated['reason_type'] ?? 'closed',
                'reason_text' => $validated['reason_text'],
                'status' => 'in_progress',
                'created_by' => $companyUser->id,
            ]);

            foreach ($reservations as $reservation) {
                ReservationChangeNoticeItem::create([
                    'notice_id' => $notice->id,
                    'company_id' => $company->id,
                    'reservation_id' => $reservation->id,
                    'customer_id' => $reservation->customer_id ?? null,
                    'customer_name' => $reservation->customer_name,
                    'customer_email' => $reservation->customer_email,
                    'customer_phone' => $reservation->customer_phone,
                    'contact_type' => !empty($reservation->customer_email) ? 'mail' : 'phone',
                    'contact_status' => !empty($reservation->customer_email) ? 'pending' : 'phone_pending',
                    'response_status' => 'waiting',
                    'response_token' => !empty($reservation->customer_email) ? Str::random(64) : null,
                ]);
            }
        });

        return back()->with('success', '予約変更連絡管理を作成しました。');
    }

    public function sendMails(ReservationChangeNotice $notice)
    {
        $company = auth()->guard('company')->user()->company;
        abort_unless((int) $notice->company_id === (int) $company->id, 403);

        $items = $notice->items()
            ->with('reservation', 'notice')
            ->whereNotNull('customer_email')
            ->whereIn('response_status', ['waiting', 'no_response'])
            ->get();

        $sent = 0;

        foreach ($items as $item) {
            $confirmUrl = route('reservation.notice.response.show', ['token' => $item->response_token]);

            Mail::to($item->customer_email)->send(
                new ReservationChangeNoticeMail($item, $confirmUrl)
            );

            $item->update([
                'contact_status' => 'mail_sent',
                'response_status' => 'mail_sent',
                'mail_sent_at' => now(),
                'last_reminder_sent_at' => now(),
                'reminder_send_count' => (int) $item->reminder_send_count + 1,
            ]);

            $sent++;
        }

        return back()->with('success', $sent . '件のメールを送信しました。');
    }

    public function markPhoneConfirmed(ReservationChangeNoticeItem $item)
    {
        $companyUser = auth()->guard('company')->user();
        $company = $companyUser->company;
        abort_unless((int) $item->company_id === (int) $company->id, 403);

        DB::transaction(function () use ($item, $companyUser) {
            $item->update([
                'contact_status' => 'closed',
                'response_status' => 'closed',
                'called_at' => now(),
                'confirmed_at' => now(),
                'cancel_reason_type' => 'shop',
                'cancelled_at' => now(),
                'cancel_processed_by' => $companyUser->id,
                'updated_by' => $companyUser->id,
                'note' => trim(($item->note ? $item->note . "\n" : '') . now()->format('Y-m-d H:i') . ' 電話確認済み'),
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
        });

        return back()->with('success', '電話確認済みにしました。');
    }

    public function updateNote(Request $request, ReservationChangeNoticeItem $item)
    {
        $companyUser = auth()->guard('company')->user();
        $company = $companyUser->company;
        abort_unless((int) $item->company_id === (int) $company->id, 403);

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $item->update([
            'note' => $validated['note'],
            'updated_by' => $companyUser->id,
        ]);

        return back()->with('success', 'メモを保存しました。');
    }
}