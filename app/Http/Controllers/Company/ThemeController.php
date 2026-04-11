<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    public function edit()
    {
        $staff = Auth::guard('company')->user();

        if (!$staff || !$staff->canDashboard('card.theme')) {
            abort(403, '権限がありません');
        }

        $company = $staff->company;

        $colors = [
            '#f43f5e',
            '#ec4899',
            '#d946ef',
            '#8b5cf6',
            '#f97316',
            '#f59e0b',
            '#84cc16',
            '#10b981',
            '#14b8a6',
            '#3b82f6',
            '#0ea5e9',
            '#06b6d4',
            '#6366f1',
            '#64748b',
            '#6b7280',
            '#a3a3a3',
            '#22c55e',
            '#eab308',
            '#ef4444',
            '#9333ea',
        ];

        return view('company.theme', compact('company', 'colors'));
    }

    public function update(Request $request)
    {
        $staff = Auth::guard('company')->user();

        if (!$staff || !$staff->canDashboard('card.theme')) {
            abort(403, '権限がありません');
        }

        $request->validate([
            'theme_color' => 'required|string',
        ]);

        $staff->company->update([
            'theme_color' => $request->theme_color,
        ]);

        return back()->with('success', 'テーマカラーを更新しました');
    }
}