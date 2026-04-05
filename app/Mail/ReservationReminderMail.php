<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build(): self
    {
        $companyName = $this->reservation->company->name ?? 'ご予約店舗';

        return $this->subject("【{$companyName}】ご予約前日のお知らせ")
            ->view('emails.reservation_reminder');
    }
}