<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class CompanyController extends Controller
{
    public function edit()
    {
        $company = Auth::guard('company')->user()->company;
        return view('company.company_edit', compact('company'));
    }

	public function update(Request $request)
	{
Log::debug('ログ１');
	    $company = Auth::guard('company')->user()->company;
	    $current = Auth::guard('company')->user();

	    if ($current->role !== 'master') {
	        abort(403);
	    }
Log::debug('ログ２');

	    $validated = $request->validate([
//		'email' => 'nullable|email',
	        'email' => 'nullable|email:rfc,dns',
	        'homepage' => 'nullable|url',
	        'theme_color' => 'required',
	        'slot_minutes' => 'required|integer|min:5|max:120',
	        'max_simultaneous_reservations' => 'required|integer|min:1|max:10',
	        'open_patterns' => 'nullable|array',
	        'regular_holidays' => 'nullable|array',
	    ]);

$dayNames = ['日','月','火','水','木','金','土'];
Log::debug('ログ２－１');
		foreach ($validated['open_patterns'] ?? [] as $weekday => $patterns) {

		    foreach ($patterns as $index => $pattern) {

		        if (!empty($pattern['open']) && !empty($pattern['close'])) {

		            if ($pattern['open'] >= $pattern['close']) {

		                $dayLabel = $dayNames[$weekday] ?? $weekday;
		                $slotNumber = $index + 1;

		                return back()
		                    ->withErrors([
		                        "open_patterns.$weekday.$index.open" =>
		                            "{$dayLabel}曜日 {$slotNumber}枠目：開始時間は終了時間より前にしてください"
		                    ])
		                    ->withInput();
		            }
		        }
		    }
		}
Log::debug('ログ３');

		$patterns = collect($request->open_patterns ?? [])
		    ->map(function ($day) {
		        return collect($day)
		            ->filter(fn($p) => !empty($p['open']) && !empty($p['close']))
		            ->values()
		            ->toArray();
		    })
		    ->toArray();

Log::debug('ログ４');


	    $updateData = [
	        'email' => $validated['email'] ?? null,
	        'homepage' => $validated['homepage'] ?? null,
	        'theme_color' => $validated['theme_color'],
	        'address' => $request->address,
	        'phone' => $request->phone,
	        'slot_minutes' => $validated['slot_minutes'],
	        'max_simultaneous_reservations' => $validated['max_simultaneous_reservations'],
	        'open_patterns' => $patterns,
	        'regular_holidays' => $validated['regular_holidays'] ?? [],
	        'holiday_is_closed' => $request->boolean('holiday_is_closed'),
	        'menu_time_priority_flag' => $request->boolean('menu_time_priority_flag'),
	    ];
Log::debug('ログ５');

	    $company->update($updateData);

		return redirect()
		    ->route('company.info.edit')
		    ->with('success', '会社情報を更新しました');

	}
}