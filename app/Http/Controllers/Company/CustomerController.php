<?php

namespace App\Http\Controllers\Company;

use App\Mail\RevisitReminderMail;
use App\Models\Customer;
use App\Models\CustomerFollowupMailLog;
use App\Models\CustomerNote;
use App\Models\CustomerPhoto;
use App\Http\Controllers\Controller;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
//画像圧縮
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class CustomerController extends Controller
{

public function index(Request $request)
{

$company = auth()->guard('company')->user()->company;

$query = Customer::where('company_id', $company->id)
    ->with(['company', 'photos' => function ($q) {
        $q->latest('id');
    }, 'latestRevisitReminderLog'])
    ->withCount([
        'reservations as no_show_count' => function ($q) {
            $q->where('status', 'no_show');
        },
    ]);


if($request->filled('keyword')){

$query->where(function($q) use ($request){

$q->where('name','like','%'.$request->keyword.'%')
->orWhere('phone','like','%'.$request->keyword.'%');

});

}

 $allCustomers = $query
 ->orderByDesc('last_visit')
 ->get();

 $revisitStatusCounts = [
     '対象' => $allCustomers->filter(fn ($customer) => $customer->revisit_reminder_status === '対象')->count(),
     '送信済み' => $allCustomers->filter(fn ($customer) => $customer->revisit_reminder_status === '送信済み')->count(),
     '予約済み' => $allCustomers->filter(fn ($customer) => $customer->revisit_reminder_status === '予約済み')->count(),
 ];

 $revisitStatus = $request->query('revisit_status');
 if (in_array($revisitStatus, array_keys($revisitStatusCounts), true)) {
     $allCustomers = $allCustomers
         ->filter(fn ($customer) => $customer->revisit_reminder_status === $revisitStatus)
         ->values();
 }

 $perPage = 30;
 $currentPage = LengthAwarePaginator::resolveCurrentPage();
 $customers = new LengthAwarePaginator(
     $allCustomers->forPage($currentPage, $perPage)->values(),
     $allCustomers->count(),
     $perPage,
     $currentPage,
     [
         'path' => LengthAwarePaginator::resolveCurrentPath(),
         'query' => $request->query(),
     ]
 );

 return view('company.customers.index', compact('customers', 'revisitStatusCounts'));

}

public function show($id)
{

$company = auth()->guard('company')->user()->company;

$customer = Customer::where('company_id',$company->id)
->with([
'reservations.staff',
'reservations.menus',
'notes',
'photos',
'company',
'latestRevisitReminderLog'
])
->findOrFail($id);

return view('company.customers.show',compact('customer'));

}

public function sendRevisitReminder($id)
{
    $company = auth()->guard('company')->user()->company;

    $customer = Customer::where('company_id', $company->id)
        ->with('company')
        ->findOrFail($id);

    $sentChannels = [];
    $sentAt = now();

    try {
        if ($company->sendsCustomerEmail() && filled($customer->email)) {
            Mail::to($customer->email)->send(
                new RevisitReminderMail($company, $customer)
            );
            $sentChannels[] = 'メール';
        }

        if (
            $company->sendsCustomerLine()
            && filled($customer->line_user_id)
            && (bool) ($customer->line_notifications_enabled ?? true)
            && (bool) ($customer->line_friend_flag ?? false)
        ) {
            $reserveUrl = url('/r/' . $company->company_code);
            $text = "【{$company->name}】その後いかがでしょうか？\n"
                . "前回のご来店から少しお日にちが経ちました。\n"
                . "ご都合のよいタイミングで、ぜひまたご利用ください。\n"
                . $reserveUrl;

            if (app(LineMessagingService::class)->pushText($company, $customer->line_user_id, $text)) {
                $customer->forceFill(['last_line_sent_at' => $sentAt])->save();
                $sentChannels[] = 'LINE';
            }
        }

        if ($sentChannels === []) {
            return back()->with('error', '再来店連絡を送信できる連絡先が登録されていません。');
        }

        CustomerFollowupMailLog::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'mail_type' => 'revisit_reminder',
            'sent_at' => $sentAt,
        ]);

        return back()->with('success', '再来店連絡を送信しました（' . implode('・', $sentChannels) . '）。');
    } catch (\Throwable $e) {
        Log::error('手動の再来店連絡送信に失敗', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'error' => $e->getMessage(),
        ]);

        return back()->with('error', '再来店連絡の送信に失敗しました。時間をおいて再度お試しください。');
    }
}

public function updateProfile(Request $request,$id)
{

$validated = $request->validate([
'name'=>'required|string|max:255',
'phone'=>'nullable|string|max:50',
'email'=>'nullable|email|max:255',
],[
'name.required'=>'顧客名を入力してください。',
'email.email'=>'メールアドレスの形式が正しくありません。',
]);

$company = auth()->guard('company')->user()->company;

$customer = Customer::where('company_id',$company->id)
->findOrFail($id);

$customer->update([
'name'=>$validated['name'],
'phone'=>$validated['phone'] ?? null,
'email'=>$validated['email'] ?? null,
]);

return back()->with('success','顧客情報を更新しました。');

}


public function note(Request $request,$id)
{

$request->validate([
'note'=>'required|string|max:2000'
]);

$company = auth()->guard('company')->user()->company;

$customer = Customer::where('company_id',$company->id)
->findOrFail($id);

CustomerNote::create([

'customer_id'=>$customer->id,
'staff_id'=>null,
'note'=>$request->note

]);

return back()->with('success','メモを保存しました');

}


public function photo(Request $request,$id)
{

$request->validate([
'photo'=>'required|image|max:5000'
]);

$company = auth()->guard('company')->user()->company;

$customer = Customer::where('company_id',$company->id)
->findOrFail($id);

//$path = $request->file('photo')->store('customers','public');

$path = null;
// 画像保存

	if ($request->hasFile('photo')) {

	    $file = $request->file('photo');

		$manager = new ImageManager(new Driver());
		$image = $manager->read($file);

		// 最大800pxに縮小
		$image->scaleDown(width: 800);

		// WebP変換
		$encoded = $image->toWebp(quality: 85);


	    $dir = public_path('companies/'.$company->id.'/customer');

	    if (!file_exists($dir)) {
	        mkdir($dir, 0755, true);
	    }

	    $filename = uniqid().'.webp';

	    $file->move($dir, $filename);

	    $path = 'companies/'.$company->id.'/customer/'.$filename;

	    //画像の保存
	    file_put_contents($path, $encoded);

	}

CustomerPhoto::create([

'customer_id'=>$customer->id,
'path'=>$path

]);


return back()->with('success','写真を追加しました');

}

// メモ削除
public function deleteNote($id)
{
    $company = auth()->guard('company')->user()->company;

    $note = CustomerNote::whereHas('customer', function($q) use ($company){
        $q->where('company_id', $company->id);
    })->findOrFail($id);

    $note->delete();

    return back()->with('success','メモを削除しました');
}


// 写真削除
public function deletePhoto($id)
{
    $company = auth()->guard('company')->user()->company;

    $photo = CustomerPhoto::whereHas('customer', function($q) use ($company){
        $q->where('company_id', $company->id);
    })->findOrFail($id);

    // ファイル削除
    if ($photo->path && file_exists(public_path($photo->path))) {
        unlink(public_path($photo->path));
    }

    $photo->delete();

    return back()->with('success','写真を削除しました');
}


}
