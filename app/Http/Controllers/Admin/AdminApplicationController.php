<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Company;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminApplicationController extends Controller
{
	public function index()
	{
	    $applications = Application::all();

	    return view('admin.applications', compact('applications'));
	}

	public function approve($id)
	{
	    DB::transaction(function () use ($id) {

	        $app = Application::findOrFail($id);

	        if ($app->status !== 'pending') {
	            return;
	        }

	        // company_code生成
	        do {
	            $companyCode = strtoupper(Str::random(8));
	        } while (Company::where('company_code', $companyCode)->exists());

	        $company = Company::create([
	            'company_code' => $companyCode,
	            'name' => $app->company_name,
	            'industry_type' => $app->industry_type,
	        ]);

	        $initialPassword = Str::random(10);

	        Staff::create([
	            'company_id' => $company->id,
	            'staff_code' => 'MASTER01',
	            'name' => $app->contact_person,
	            'password' => Hash::make($initialPassword),
	            'role' => 'master'
	        ]);

	        $app->update(['status' => 'approved']);

	        Mail::raw("
	企業コード: {$companyCode}
	担当者コード: MASTER01
	パスワード: {$initialPassword}
	ログインURL: https://reserve.tocoluca.com/company/login
	        ", function ($message) use ($app) {
	            $message->to($app->email)
	                ->subject('予約システム登録完了のお知らせ');
	        });
	    });

	    return back()->with('success', '承認完了');
	}
	public function reject($id)
	{
	    $app = Application::findOrFail($id);
	    $app->update(['status' => 'rejected']);

	    return back()->with('success', '拒否しました');
	}

}