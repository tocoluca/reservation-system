<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Company $company;
    public Reservation $reservation;
    public string $reviewUrl;

    public function __construct(Company $company, Reservation $reservation, string $reviewUrl)
    {
        $this->company = $company;
        $this->reservation = $reservation;
        $this->reviewUrl = $reviewUrl;
    }

    public function build()
    {
        return $this->subject('【ご来店ありがとうございました】口コミのご協力をお願いします')
            ->text('emails.review_request');
    }
}