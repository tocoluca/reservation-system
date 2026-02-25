<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('company.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'company_code' => 'required',
            'staff_code'   => 'required',
            'password'     => 'required'
        ]);

        $credentials = [
            'staff_code' => $request->staff_code,
            'password'   => $request->password,
        ];

        if (Auth::guard('company')->attempt($credentials)) {

            $staff = Auth::guard('company')->user();

            // company_code一致確認
            if ($staff->company->company_code !== $request->company_code) {
                Auth::guard('company')->logout();
                return back()->with('error', '企業コードが違います');
            }

            // 初期パスワードの変更
		if ($staff->force_password_change) {
		    return redirect()->route('company.password.change');
		}

            return redirect()->route('company.dashboard');
        }

        return back()->with('error', 'ログイン失敗');
    }

    public function logout()
    {
        Auth::guard('company')->logout();
        return redirect()->route('company.login');
    }
}