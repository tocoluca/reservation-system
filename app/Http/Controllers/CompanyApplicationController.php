<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyApplicationController extends Controller
{
    public function create()
    {
        return view('apply.company_application');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'industry_type'  => ['required', 'in:beauty,dental'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => ['required', 'string', 'max:255'],
            'message'        => ['nullable', 'string', 'max:3000'],
            'agree_terms'    => ['accepted'],
        ], [
            'company_name.required'   => '企業名を入力してください。',
            'industry_type.required'  => '業種を選択してください。',
            'industry_type.in'        => '業種の選択が不正です。',
            'contact_person.required' => '担当者名を入力してください。',
            'email.required'          => 'メールアドレスを入力してください。',
            'email.email'             => 'メールアドレスの形式が正しくありません。',
            'phone.required'          => '電話番号を入力してください。',
            'message.max'             => '補足・お問い合わせは3000文字以内で入力してください。',
            'agree_terms.accepted'    => '利用規約・プライバシーポリシー・特定商取引法に基づく表記への同意が必要です。',
        ]);

        $existsPending = Application::where('email', $data['email'])
            ->where('status', 'pending')
            ->exists();

        if ($existsPending) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'このメールアドレスでは、現在審査中の申請があります。管理者からの連絡をお待ちください。'
                ]);
        }

        $existsApproved = Application::where('email', $data['email'])
            ->where('status', 'approved')
            ->exists();

        if ($existsApproved) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'このメールアドレスでは、すでに承認済みの申請があります。ログイン情報をご確認ください。'
                ]);
        }

        $application = Application::create([
            'company_name'   => $data['company_name'],
            'industry_type'  => $data['industry_type'],
            'contact_person' => $data['contact_person'],
            'email'          => $data['email'],
            'phone'          => $data['phone'],
            'message'        => $data['message'] ?? null,
            'status'         => 'pending',
        ]);

        $adminEmails = [];

        if (class_exists(Admin::class)) {
            $adminEmails = Admin::query()
                ->whereNotNull('email')
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (!empty($adminEmails)) {
            Mail::send(
                'emails.admin_company_application_notification',
                ['application' => $application],
                function ($message) use ($adminEmails, $application) {
                    $message->to($adminEmails)
                        ->subject('【予約システム】新しい企業利用申請（' . $application->company_name . '）');
                }
            );
        }

        if (!empty($application->email)) {
            Mail::send(
                'emails.company_application_received',
                ['application' => $application],
                function ($message) use ($application) {
                    $message->to($application->email, $application->contact_person)
                        ->subject('【予約システム】利用申請を受け付けました（受付番号: ' . $application->id . '）');
                }
            );
        }

        return redirect()
            ->route('company.application.complete', ['id' => $application->id]);
    }

    public function complete(Request $request)
    {
        $applicationId = $request->query('id');

        return view('apply.company_application_complete', compact('applicationId'));
    }
}