<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\Staff;
use App\Mail\MasterCreatedMail;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class CompanyController extends Controller
{
    public function create()
    {
        return view('admin.company_create');
    }

	public function store(Request $request)
	{
	    $request->validate([
	        'name' => 'required',
	        'industry_type' => 'required',
	        'staff_code' => 'required|unique:staff,staff_code',
		'staff_password' => ['required', Password::min(8)],
		'email' => 'required|email:rfc,dns'

	    ]);

	    $code = strtoupper(Str::random(8));

	    DB::transaction(function () use ($request, $code) {

	        $company = Company::create([
	            'company_code' => $code,
	            'name' => $request->name,
	            'email' => $request->email,
	            'industry_type' => $request->industry_type,
	            'theme_color' => '#3b82f6'
	        ]);

	        Staff::create([
	            'company_id' => $company->id,
	            'staff_code' => $request->staff_code,
	            'name' => $request->staff_name,
	            'password' => Hash::make($request->staff_password),
	            'role' => 'master',
	            'is_reservable' => false,
	            'priority_order' => 0,
		    'force_password_change' => true
	        ]);

	       // メール送信
	        Mail::to($request->email)
	            ->send(new MasterCreatedMail(
	                $company,
			$request->staff_code,
	                $request->staff_name,
	                $request->staff_password
	            ));

	        // メール送信（必要なら email フィールドを使う）
 
/*
	        Mail::raw("
	企業コード: {$code}
	担当者コード: {$request->staff_code}
	パスワード: {$request->staff_password}
	ログインURL: https://reserve.tocoluca.com/company/login
	        ", function ($message) use ($request) {
	            $message->to($request->email)
	                ->subject('予約システム登録完了のお知らせ');
	        });
*/
	    });

	    return redirect()->route('admin.dashboard')
	        ->with('success','企業とマスターを登録しました');
	}
	private function generateCompanyCode()
	{
	    do {
	        $code = strtoupper(Str::random(8));
	    } while (Company::where('company_code', $code)->exists());

	    return $code;
	}
	public function index(Request $request)
	{
	    $query = Company::query();

	    if ($request->keyword) {
	        $query->where(function ($q) use ($request) {
	            $q->where('name', 'like', "%{$request->keyword}%")
	              ->orWhere('company_code', 'like', "%{$request->keyword}%");
	        });
	    }

	    $companies = $query->latest()->paginate(10);

	    return view('admin.company_index', compact('companies'));
	}

	public function toggle($id)
	{
	    $company = Company::findOrFail($id);
	    $company->is_active = !$company->is_active;
	    $company->save();

	    return back()->with('success', '状態を更新しました');
	}

}