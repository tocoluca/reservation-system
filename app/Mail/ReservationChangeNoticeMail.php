<?php

namespace App\Mail;

use App\Models\ReservationChangeNoticeItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationChangeNoticeMail extends Mailable
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
        return $this->subject('【重要】ご予約変更のご確認をお願いいたします')
            ->view('emails.reservation_change_notice')
            ->with([
                'item' => $this->item,
                'reservation' => $this->item->reservation,
                'notice' => $this->item->notice,
                'confirmUrl' => $this->confirmUrl,
            ]);
    }
}