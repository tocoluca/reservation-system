<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MasterCreatedMail extends Mailable
{
    public $company;
    public $staff_code;
    public $staff_name;
    public $password;

    public function __construct($company, $staff_code,$staff_name, $password)
    {
        $this->company = $company;
        $this->staff_code = $staff_code;
        $this->staff_name = $staff_name;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('【予約システム】アカウント発行のお知らせ')
            ->text('emails.master-created');
    }
}