<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('company.password-change');
    }

    public function update(Request $request)
    {
        $staff = Auth::guard('company')->user();

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required'  => '新しいパスワードを入力してください。',
            'password.min'       => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => '新しいパスワードと確認用パスワードが一致しません。',
        ]);

        $staff->password = Hash::make($request->password);
        $staff->force_password_change = false;
        $staff->save();

        if (!$staff->company->is_initialized) {
            return redirect()
                ->route('company.setup')
                ->with('success', 'パスワードを変更しました。続けて初回設定ガイドをご確認ください。');
        }

        return redirect()
            ->route('company.dashboard')
            ->with('success', 'パスワードを変更しました。');
    }
}