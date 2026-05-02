<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CompanyInit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('company')->user();

        if (!$staff) {
            return redirect()->route('company.login');
        }

        $company = $staff->company;

        if (!$company) {
            Auth::guard('company')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('company.login')
                ->with('error', '企業情報が見つかりません。');
        }

        // 初回パスワード変更が必要なら、そちらを優先
        if ($staff->force_password_change && !(bool) $request->session()->get('admin_impersonating_company', false)) {
            if (
                !$request->routeIs('company.password.change') &&
                !$request->routeIs('company.password.change.update') &&
                !$request->routeIs('company.logout')
            ) {
                return redirect()->route('company.password.change');
            }

            return $next($request);
        }

        // 初期設定未完了でも入ってよい画面
        $setupAllowedRoutes = [
            // 初期設定ガイド
            'company.setup',
            'company.setup.complete',

            // 企業情報
            'company.info.edit',
            'company.info.update',

            // 営業日カレンダー
            'company.calendar.index',
            'company.calendar.toggle',
            'company.calendar.updateTime',
            'company.calendar.deleteTime',
            'company.calendar.year',
            'company.calendar.bulkYearWeekday',
            'company.calendar.bulkWeekday',
            'company.calendar.bulkYearOpenWeekday',

            // 担当者
            'company.staff.index',
            'company.staff.create',
            'company.staff.store',
            'company.staff.edit',
            'company.staff.update',
            'company.staff.destroy',
            'company.staff.reset-password',

            // 自分のプロフィール
            'company.my-profile',
            'company.my-profile.update',

            // メニュー
            'company.menu.index',
            'company.menu.create',
            'company.menu.store',
            'company.menu.edit',
            'company.menu.update',
            'company.menu.destroy',

            // カテゴリー・タグ
            'company.menu.settings',
            'company.menu.category.store',
            'company.menu.tag.store',
            'company.menu.category.delete',
            'company.menu.tag.delete',

            // メニュー対応スタッフ
            'company.menu-staff.index',
            'company.menu-staff.update',

            // シフトパターン
            'company.shift-patterns',
            'company.shift-patterns.store',
            'company.shift-patterns.delete',

            // 基本シフト
            'company.staff-default-shifts',
            'company.staff-default-shifts.update',

            // 月シフト
            'company.staff-shifts',
            'company.staff-shifts.generate',
            'company.staff-shifts.update',
            'company.staff-shifts.copy',

            // ログアウト
            'company.logout',
        ];

        // 初期ガイド未完了なら、許可画面以外は setup へ誘導
        if (!$company->is_initialized) {
            $currentRouteName = optional($request->route())->getName();

            if (!$currentRouteName || !in_array($currentRouteName, $setupAllowedRoutes, true)) {
                return redirect()->route('company.setup')
                    ->with('error', '最初に初期設定を完了してください。');
            }
        }

        return $next($request);
    }
}
