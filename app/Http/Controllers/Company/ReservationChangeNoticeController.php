<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Mail\ReservationChangeNoticeMail;
use App\Models\Reservation;
use App\Models\ReservationChangeNotice;
use App\Models\ReservationChangeNoticeItem;
use App\Services\LineMessagingService;
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

        $notice->load(['items.reservation.customer']);

        return view('company.reservation_change_notices.show', compact('notice'));
    }

    public function createFromClosedDate(Request $request)
    {
        $companyUser = auth()->guard('company')->user();
        $company = $companyUser->company;

        $validated = $request->validate([
            'target_date'  => ['required', 'date'],
            'reason_type'  => ['nullable', 'string', 'max:50'],
            'reason_text'  => ['required', 'string', 'max:2000'],
            'title'        => ['nullable', 'string', 'max:255'],
        ]);

        $targetDate = $validated['target_date'];

        $reservations = Reservation::query()
            ->with('customer')
            ->where('company_id', $company->id)
            ->whereDate('start_at', $targetDate)
            ->where('status', 'reserved')
            ->get();

        if ($reservations->isEmpty()) {
            return back()->with('error', '対象となる予約がありません。');
        }

        DB::transaction(function () use ($validated, $company, $companyUser, $reservations, $targetDate) {
            $notice = ReservationChangeNotice::create([
                'company_id'   => $company->id,
                'title'        => $validated['title'] ?: ($targetDate . ' の営業日変更による予約変更連絡'),
                'target_date'  => $targetDate,
                'reason_type'  => $validated['reason_type'] ?? 'closed',
                'reason_text'  => $validated['reason_text'],
                'status'       => 'in_progress',
                'created_by'   => $companyUser->id,
            ]);

            foreach ($reservations as $reservation) {
                $customer = $reservation->customer;

                $canLine = $customer
                    && !empty($customer->line_user_id)
                    && (bool) ($customer->line_notifications_enabled ?? true);

                $canMail = !empty($reservation->customer_email);

                $contactType = $canLine ? 'line' : ($canMail ? 'mail' : 'phone');
                $contactStatus = ($canLine || $canMail) ? 'pending' : 'phone_pending';
                $responseToken = ($canLine || $canMail) ? Str::random(64) : null;

                ReservationChangeNoticeItem::create([
                    'notice_id'        => $notice->id,
                    'company_id'       => $company->id,
                    'reservation_id'   => $reservation->id,
                    'customer_id'      => $reservation->customer_id ?? null,
                    'customer_name'    => $reservation->customer_name,
                    'customer_email'   => $reservation->customer_email,
                    'customer_phone'   => $reservation->customer_phone,
                    'contact_type'     => $contactType,
                    'contact_status'   => $contactStatus,
                    'response_status'  => 'waiting',
                    'response_token'   => $responseToken,
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
            ->with('reservation.customer', 'notice')
            ->whereIn('response_status', ['waiting', 'no_response'])
            ->get();

        $sent = 0;

        foreach ($items as $item) {
            $reservation = $item->reservation;
            $customer = $reservation?->customer;

            $canLine = $customer
                && !empty($customer->line_user_id)
                && (bool) ($customer->line_notifications_enabled ?? true);

            $canMail = !empty($item->customer_email);

            if (!$canLine && !$canMail) {
                continue;
            }

            $confirmUrl = $item->response_token
                ? route('reservation.notice.response.show', ['token' => $item->response_token])
                : null;

            $sentMail = false;
            $sentLine = false;

            if ($canMail && $confirmUrl) {
                Mail::to($item->customer_email)->send(
                    new ReservationChangeNoticeMail($item, $confirmUrl)
                );
                $sentMail = true;
            }

            if ($canLine && $confirmUrl) {
                $text = "【{$company->name}】\n"
                    . "ご予約内容の変更についてお願いがあります。\n"
                    . "お手数ですが、詳細をご確認ください。\n"
                    . $confirmUrl;

                $lineSent = app(LineMessagingService::class)->pushText(
                    $company,
                    $customer->line_user_id,
                    $text
                );

                if ($lineSent) {
                    $customer->forceFill([
                        'last_line_sent_at' => now(),
                    ])->save();

                    $sentLine = true;
                }
            }

            if (!$sentMail && !$sentLine) {
                continue;
            }

            $item->update([
                'contact_type'           => $sentLine ? ($sentMail ? 'line+mail' : 'line') : 'mail',
                'contact_status'         => $sentLine ? ($sentMail ? 'line+mail_sent' : 'line_sent') : 'mail_sent',
                // 既存の件数集計を壊さないため response_status は mail_sent に寄せる
                'response_status'        => 'mail_sent',
                'mail_sent_at'           => $sentMail ? now() : $item->mail_sent_at,
                'last_reminder_sent_at'  => now(),
                'reminder_send_count'    => (int) $item->reminder_send_count + 1,
            ]);

            $sent++;
        }

        return back()->with('success', $sent . '件の通知を送信しました。');
    }

    public function markPhoneConfirmed(ReservationChangeNoticeItem $item)
    {
        $companyUser = auth()->guard('company')->user();
        $company = $companyUser->company;
        abort_unless((int) $item->company_id === (int) $company->id, 403);

        DB::transaction(function () use ($item, $companyUser) {
            $item->update([
                'contact_status'      => 'closed',
                'response_status'     => 'closed',
                'called_at'           => now(),
                'confirmed_at'        => now(),
                'cancel_reason_type'  => 'shop',
                'cancelled_at'        => now(),
                'cancel_processed_by' => $companyUser->id,
                'updated_by'          => $companyUser->id,
                'note'                => trim(($item->note ? $item->note . "\n" : '') . now()->format('Y-m-d H:i') . ' 電話確認済み'),
            ]);

            $reservation = $item->reservation;

            if ($reservation && !in_array($reservation->status, ['cancelled', 'completed'], true)) {
                $reservation->update([
                    'status'           => 'cancelled',
                    'cancelled_type'   => 'shop',
                    'cancelled_at'     => now(),
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
            'note'       => $validated['note'],
            'updated_by' => $companyUser->id,
        ]);

        return back()->with('success', 'メモを保存しました。');
    }

	public function destroy(ReservationChangeNotice $notice)
	{
	    $company = auth()->guard('company')->user()->company;

	    abort_unless((int) $notice->company_id === (int) $company->id, 403);

	    DB::transaction(function () use ($notice) {
	        // 紐づく明細を先に削除
	        $notice->items()->delete();

	        // 案件本体を削除
	        $notice->delete();
	    });

	    return redirect()
	        ->route('company.reservation_change_notices.index')
	        ->with('success', '予約変更連絡管理を削除しました。');
	}

}
