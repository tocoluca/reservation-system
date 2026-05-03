<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Mail\Mailable;

class CompanyMasterPasswordResetMail extends Mailable
{
    public function __construct(
        public Company $company,
        public string $initialPassword,
        public array $masterStaffCodes
    ) {
    }

    public function build()
    {
        return $this
            ->from('system@tocoluca.com', 'TOCOLUCA System')
            ->subject('【TOCOLUCA】マスター権限パスワード初期化のお知らせ')
            ->view('emails.company-master-password-reset');
    }
}
