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
            '#7f1d1d',
            '#991b1b',
            '#b91c1c',
            '#881337',
            '#9f1239',
            '#be123c',
            '#831843',
            '#9d174d',
            '#be185d',
            '#701a75',
            '#86198f',
            '#a21caf',
            '#581c87',
            '#6b21a8',
            '#7e22ce',
            '#4c1d95',
            '#5b21b6',
            '#6d28d9',
            '#312e81',
            '#3730a3',
            '#4338ca',
            '#172554',
            '#1e3a8a',
            '#1e40af',
            '#1d4ed8',
            '#0c4a6e',
            '#075985',
            '#0369a1',
            '#164e63',
            '#155e75',
            '#0e7490',
            '#083344',
            '#134e4a',
            '#115e59',
            '#0f766e',
            '#064e3b',
            '#065f46',
            '#047857',
            '#052e16',
            '#14532d',
            '#166534',
            '#15803d',
            '#1a2e05',
            '#365314',
            '#3f6212',
            '#4d7c0f',
            '#422006',
            '#713f12',
            '#854d0e',
            '#92400e',
            '#431407',
            '#78350f',
            '#9a3412',
            '#c2410c',
            '#7c2d12',
            '#451a03',
            '#5c2e0f',
            '#6b3f1d',
            '#783f04',
            '#8a4b0f',
            '#0f172a',
            '#1e293b',
            '#334155',
            '#475569',
            '#111827',
            '#1f2937',
            '#374151',
            '#4b5563',
            '#18181b',
            '#27272a',
            '#3f3f46',
            '#52525b',
            '#171717',
            '#262626',
            '#404040',
            '#525252',
            '#1c1917',
            '#292524',
            '#44403c',
            '#57534e',
            '#3b0764',
            '#4a044e',
            '#500724',
            '#4c0519',
            '#450a0a',
            '#2e1065',
            '#1e1b4b',
            '#082f49',
            '#042f2e',
            '#022c22',
            '#052e1a',
            '#1f2933',
            '#263238',
            '#2f3e46',
            '#3d405b',
            '#463f3a',
            '#4a2c2a',
            '#5b2333',
            '#5f0f40',
            '#6a040f',
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
