<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class RevisitMail extends Mailable
{

public $customer;

public function __construct($customer)
{

$this->customer = $customer;

}

public function build()
{

return $this->subject('そろそろご来店の時期です')
->text('emails.revisit');

}

}