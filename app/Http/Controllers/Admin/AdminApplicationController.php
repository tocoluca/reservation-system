<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('industry_type')) {
            $query->where('industry_type', $request->industry_type);
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('company_name', 'like', "%{$keyword}%")
                  ->orWhere('contact_person', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $applications = $query
            ->orderByRaw("
                CASE status
                    WHEN 'pending' THEN 0
                    WHEN 'approved' THEN 1
                    WHEN 'rejected' THEN 2
                    ELSE 9
                END
            ")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'all'      => Application::count(),
            'pending'  => Application::where('status', 'pending')->count(),
            'approved' => Application::where('status', 'approved')->count(),
            'rejected' => Application::where('status', 'rejected')->count(),
        ];

        return view('admin.applications', compact('applications', 'stats'));
    }

    public function show($id)
    {
        $application = Application::with('approvedCompany')->findOrFail($id);

        return response()->json([
            'id'                     => $application->id,
            'company_name'           => $application->company_name,
            'industry_type'          => $application->industry_type,
            'industry_label'         => $application->industry_label,
            'contact_person'         => $application->contact_person,
            'email'                  => $application->email,
            'phone'                  => $application->phone,
            'message'                => $application->message,
            'status'                 => $application->status,
            'status_label'           => $application->status_label,
            'reject_reason'          => $application->reject_reason,
            'reviewed_at'            => optional($application->reviewed_at)->format('Y-m-d H:i'),
            'approved_company_id'    => $application->approved_company_id,
            'initial_staff_code'     => $application->initial_staff_code,
            'initial_password_plain' => $application->initial_password_plain,
            'login_url'              => $application->login_url,
            'created_at'             => optional($application->created_at)->format('Y-m-d H:i'),
            'updated_at'             => optional($application->updated_at)->format('Y-m-d H:i'),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'send_mail' => ['nullable', 'in:0,1'],
        ]);

        $application = Application::findOrFail($id);

        if ($application->status !== 'pending') {
            return back()->with('error', 'この申請はすでに処理済みです。');
        }

        DB::transaction(function () use ($application, $request) {
            do {
                $companyCode = strtoupper(Str::random(8));
            } while (Company::where('company_code', $companyCode)->exists());

            $company = Company::create([
                'company_code'  => $companyCode,
                'name'          => $application->company_name,
                'industry_type' => $application->industry_type,
            ]);

            $initialPassword = Str::random(10);
            $staffCode = 'MASTER01';
            $loginUrl = url('/company/login');

            Staff::create([
                'company_id' => $company->id,
                'staff_code' => $staffCode,
                'name'       => $application->contact_person,
                'password'   => Hash::make($initialPassword),
                'role'       => 'master',
            ]);

            $application->update([
                'status'                 => 'approved',
                'reject_reason'          => null,
                'reviewed_at'            => Carbon::now(),
                'approved_company_id'    => $company->id,
                'initial_staff_code'     => $staffCode,
                'initial_password_plain' => $initialPassword,
                'login_url'              => $loginUrl,
            ]);

            if ((string) $request->input('send_mail', '1') === '1') {
                Mail::raw(
"{$application->company_name} ご担当者様

このたびはお申込みありがとうございます。
審査が完了し、利用開始情報を発行しました。

企業コード：{$companyCode}
担当者コード：{$staffCode}
初期パスワード：{$initialPassword}
ログインURL：{$loginUrl}

初回ログイン後、必要に応じてパスワード変更をお願いいたします。",
                    function ($message) use ($application) {
                        $message->to($application->email)
                                ->subject('【予約システム】申請承認のお知らせ');
                    }
                );
            }
        });

        return back()->with('success', '申請を承認し、企業アカウントを作成しました。');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
            'send_mail'     => ['nullable', 'in:0,1'],
        ], [
            'reject_reason.required' => '却下理由を入力してください。',
            'reject_reason.max'      => '却下理由は1000文字以内で入力してください。',
        ]);

        $application = Application::findOrFail($id);

        if ($application->status !== 'pending') {
            return back()->with('error', 'この申請はすでに処理済みです。');
        }

        $application->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reject_reason,
            'reviewed_at'   => Carbon::now(),
        ]);

        if ((string) $request->input('send_mail', '1') === '1') {
            Mail::raw(
"{$application->company_name} ご担当者様

このたびはお申込みありがとうございました。
審査の結果、今回はご利用開始を見送らせていただくこととなりました。

理由：
{$application->reject_reason}

ご不明点がありましたらお問い合わせください。",
                function ($message) use ($application) {
                    $message->to($application->email)
                            ->subject('【予約システム】申請結果のお知らせ');
                }
            );
        }

        return back()->with('success', '申請を却下しました。');
    }

    public function pending($id)
    {
        $application = Application::findOrFail($id);

        $application->update([
            'status'        => 'pending',
            'reject_reason' => null,
            'reviewed_at'   => null,
        ]);

        return back()->with('success', '申請状態を審査待ちに戻しました。');
    }
}