<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RevisitReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Company $company;
    public Customer $customer;

    public function __construct(Company $company, Customer $customer)
    {
        $this->company = $company;
        $this->customer = $customer;
    }

    public function build()
    {
        return $this->subject('【' . $this->company->name . '】そろそろ次回ご予約のおすすめです')
            ->view('emails.revisit_reminder');
    }
}