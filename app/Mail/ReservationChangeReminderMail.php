<?php

namespace App\Mail;

use App\Models\ReservationChangeNoticeItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationChangeReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public ReservationChangeNoticeItem $item;
    public string $confirmUrl;

    public function __construct(ReservationChangeNoticeItem $item, string $confirmUrl)
    {
        $this->item = $item;
        $this->confirmUrl = $confirmUrl;
    }

    public function build()
    {
        return $this->subject('【再送】ご予約変更のご確認をお願いいたします')
            ->view('emails.reservation_change_reminder')
            ->with([
                'item' => $this->item,
                'reservation' => $this->item->reservation,
                'notice' => $this->item->notice,
                'confirmUrl' => $this->confirmUrl,
            ]);
    }
}