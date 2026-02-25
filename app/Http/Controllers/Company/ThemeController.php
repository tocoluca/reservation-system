<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    /**
     * テーマ設定画面表示
     */
    public function edit()
    {
        $staff = Auth::guard('company')->user();

        // マスターのみ変更可能
        if ($staff->role !== 'master') {
            abort(403, '権限がありません');
        }

        $company = $staff->company;

        // 20色プリセット（美容＋歯科統合）
        $colors = [
            '#f43f5e', // rose
            '#ec4899', // pink
            '#d946ef', // fuchsia
            '#8b5cf6', // purple
            '#f97316', // orange
            '#f59e0b', // amber
            '#84cc16', // lime
            '#10b981', // emerald
            '#14b8a6', // teal
            '#3b82f6', // blue
            '#0ea5e9', // sky
            '#06b6d4', // cyan
            '#6366f1', // indigo
            '#64748b', // slate
            '#6b7280', // gray
            '#a3a3a3', // neutral
            '#22c55e', // green
            '#eab308', // yellow
            '#ef4444', // red
            '#9333ea', // violet
        ];

        return view('company.theme', compact('company', 'colors'));
    }

    /**
     * テーマ保存
     */
    public function update(Request $request)
    {
        $staff = Auth::guard('company')->user();

        if ($staff->role !== 'master') {
            abort(403, '権限がありません');
        }

        $request->validate([
            'theme_color' => 'required|string'
        ]);

        $staff->company->update([
            'theme_color' => $request->theme_color
        ]);

        return back()->with('success', 'テーマカラーを更新しました');
    }
}