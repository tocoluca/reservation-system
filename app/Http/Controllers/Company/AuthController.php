<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function logout(Request $request)
    {
        Auth::guard('company')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('company.login');
    }
}
