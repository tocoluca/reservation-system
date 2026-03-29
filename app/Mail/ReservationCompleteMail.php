<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCompleteMail extends Mailable
{
    use Queueable, SerializesModels;

    public Company $company;
    public Reservation $reservation;

    public function __construct(Company $company, Reservation $reservation)
    {
        $this->company = $company;
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('【' . $this->company->name . '】ご予約ありがとうございます')
            ->view('emails.reservation_complete');
    }
}