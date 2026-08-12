<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

use App\Models\Customer;
use App\Mail\RevisitMail;
use Illuminate\Support\Facades\Mail;

class SendRevisitDM extends Command
{

protected $signature = 'dm:revisit';

protected $description = '次回来店DM送信';

public function handle()
{

$targetDate = Carbon::today()->addDays(7);

$customers = Customer::whereDate(
'next_visit_at',
$targetDate
)->get();

foreach($customers as $customer){

 if(!$customer->email || !$customer->company || !$customer->company->sendsCustomerEmail()){
continue;
}

Mail::to($customer->email)
->send(new RevisitMail($customer));

}

$this->info('DM送信完了');

}

}
