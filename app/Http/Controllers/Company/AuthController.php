<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\CompanyMasterPasswordResetMail;
use App\Models\Company;
use App\Models\Staff;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('company')->check()) {
            Auth::guard('company')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('company.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'company_code' => ['required', 'string'],
            'staff_code'   => ['required', 'string'],
            'password'     => ['required', 'string'],
        ], [
            'company_code.required' => '企業コードを入力してください。',
            'staff_code.required'   => 'スタッフコードを入力してください。',
            'password.required'     => 'パスワードを入力してください。',
        ]);

        $companyCode = trim($request->company_code);
        $staffCode   = trim($request->staff_code);
        $password    = $request->password;

        $company = Company::where('company_code', $companyCode)->first();

        if (!$company) {
            return back()
                ->withInput($request->only('company_code', 'staff_code'))
                ->with('error', '企業コードまたはログイン情報が正しくありません。');
        }

        $staff = Staff::where('company_id', $company->id)
            ->where('staff_code', $staffCode)
            ->first();

        if (!$staff) {
            return back()
                ->withInput($request->only('company_code', 'staff_code'))
                ->with('error', '企業コードまたはログイン情報が正しくありません。');
        }

        if (!Hash::check($password, $staff->password)) {
            return back()
                ->withInput($request->only('company_code', 'staff_code'))
                ->with('error', '企業コードまたはログイン情報が正しくありません。');
        }

        Auth::guard('company')->login($staff);
        $request->session()->regenerate();

        // 初回パスワード変更が必要ならそちらを優先
        if ($staff->force_password_change) {
            return redirect()->route('company.password.change');
        }

        // 初回案内が未確認なら設定ガイドへ
        if (!$company->is_initialized) {
            return redirect()->route('company.setup');
        }

        return redirect()->route('company.dashboard');
    }

    public function resetMasterPassword(Request $request)
    {
        $validated = $request->validate([
            'company_code' => ['required', 'string'],
            'email' => ['required', 'email'],
        ], [
            'company_code.required' => '企業コードを入力してください。',
            'email.required' => '登録済みメールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
        ]);

        $companyCode = trim($validated['company_code']);
        $email = mb_strtolower(trim($validated['email']));

        $company = Company::where('company_code', $companyCode)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$company) {
            return back()
                ->withInput($request->only('company_code', 'email'))
                ->with('error', '企業コードもしくは登録済みメールアドレスが間違っています');
        }

        $masters = Staff::where('company_id', $company->id)
            ->where('role', 'master')
            ->orderBy('id')
            ->get();

        if ($masters->isEmpty()) {
            return back()
                ->withInput($request->only('company_code', 'email'))
                ->with('error', 'マスター権限の担当者が見つかりません。管理者へお問い合わせください。');
        }

        $initialPassword = 'Toco-' . Str::upper(Str::random(4)) . '-' . Str::random(6);

        foreach ($masters as $master) {
            $master->password = Hash::make($initialPassword);
            $master->force_password_change = true;
            $master->save();
        }

        Mail::to($company->email)->send(
            new CompanyMasterPasswordResetMail(
                $company,
                $initialPassword,
                $masters->pluck('staff_code')->filter()->values()->all()
            )
        );

        return back()->with('success', 'マスター権限のパスワードを初期化し、登録済みメールアドレスへ送信しました。');
    }

    public function logout(Request $request)
    {
        if ((bool) $request->session()->get('admin_impersonating_company', false)) {
            Auth::guard('company')->logout();

            $request->session()->forget([
                'admin_impersonating_company',
                'admin_impersonated_company_id',
                'admin_impersonated_staff_id',
            ]);
            $request->session()->regenerateToken();

            return redirect()->route('admin.company.index')
                ->with('success', '代理ログインを終了しました。');
        }

        Auth::guard('company')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('company.login');
    }
}
