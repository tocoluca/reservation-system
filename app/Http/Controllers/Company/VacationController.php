<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VacationController extends Controller
{
    /* ==========================
       権限レベル定義
    ========================== */
    private function roleLevel($role)
    {
        return match($role) {
            'master' => 4,
            'area_leader' => 3,
            'leader' => 2,
            default => 1
        };
    }

    private function isLeaderOrAbove()
    {
        $role = Auth::guard('company')->user()->role;
        return $this->roleLevel($role) >= 2;
    }

    /* ==========================
       一覧表示
    ========================== */
    public function index()
    {
        $current = Auth::guard('company')->user();

//全員に見せる
            $vacations = Vacation::whereHas('staff', function($q) use ($current) {
                $q->where('company_id', $current->company_id);
            })->latest()->get();

//        if ($this->isLeaderOrAbove()) {
            // 上位権限は自社全体表示
//            $vacations = Vacation::whereHas('staff', function($q) use ($current) {
//                $q->where('company_id', $current->company_id);
//            })->latest()->get();
//        } else {
            // 一般メンバーは自分のみ
//            $vacations = Vacation::where('staff_id', $current->id)
//                ->latest()->get();
//        }

        return view('company.vacation.index', compact('vacations','current'));
    }

    /* ==========================
       申請画面
    ========================== */
	public function create()
	{
	    $staff = auth()->guard('company')->user();

	    return view('company.vacation.create', compact('staff'));
	}

    /* ==========================
       申請保存
    ========================== */
	    // 🔵 選択された担当者を取得
//	    $staff = \App\Models\Staff::where('company_id', $company->id)
//	        ->findOrFail($request->staff_id);
	public function store(Request $request)
	{
	    $company = auth()->guard('company')->user()->company;
	    $staff   = auth()->guard('company')->user();

	    $isFullDay = $request->boolean('is_full_day');

	    if ($isFullDay) {

	        $request->validate([
	            'vacation_date' => 'required|date'
	        ]);

	        $start = Carbon::parse($request->vacation_date)->startOfDay();
	        $end   = Carbon::parse($request->vacation_date)->endOfDay();

	    } else {

	        $request->validate([
	            'start_date' => 'required|date',
	            'start_time' => 'required',
	            'end_date'   => 'required|date',
	            'end_time'   => 'required',
	        ]);

	        $start = Carbon::parse($request->start_date.' '.$request->start_time);
	        $end   = Carbon::parse($request->end_date.' '.$request->end_time);

	        if ($start >= $end) {
	            return back()->withErrors([
	                'end_time' => '終了は開始より後にしてください'
	            ]);
	        }
	    }

	    DB::transaction(function () use ($staff, $start, $end, $isFullDay) {

	        Vacation::create([
	            'staff_id'   => $staff->id,
	            'start_at'   => $start,
	            'end_at'     => $end,
	            'status'     => 'pending',
	            'is_full_day'=> $isFullDay
	        ]);
	    });

	    return redirect()->route('company.vacation.index')
	        ->with('success', '休暇申請を送信しました');
	}
/* ==========================
承認
========================== */
    public function approve(Vacation $vacation)
    {
        if (!$this->isLeaderOrAbove()) {
            abort(403, '承認権限がありません');
        }

        $vacation->update([
            'status' => 'approved'
        ]);

        return back()->with('success','承認しました');
    }

    /* ==========================
       却下
    ========================== */
    public function reject(Vacation $vacation)
    {
        if (!$this->isLeaderOrAbove()) {
            abort(403);
        }

        $vacation->update([
            'status' => 'rejected'
        ]);

        return back()->with('success','却下しました');
    }

    /* ==========================
       削除（自分の申請のみ）
    ========================== */
    public function destroy(Vacation $vacation)
    {
        $current = Auth::guard('company')->user();

        if ($vacation->staff_id !== $current->id &&
            !$this->isLeaderOrAbove()) {

            abort(403);
        }

        $vacation->delete();

        return back()->with('success','削除しました');
    }

    /* ==========================
       承認済み➡取り消し
    ========================== */

	public function cancel(Vacation $vacation)
	{
	    $current = auth()->guard('company')->user();

	    // 🔴 リーダー以上のみ
	    if (!in_array($current->role, ['leader','area_leader','master'])) {
	        abort(403);
	    }

	    // 🔴 承認済のみ取消可能
	    if ($vacation->status !== 'approved') {
	        return back()->withErrors(['status' => '承認済の休暇のみ取消できます']);
	    }

	    // 🔴 同一企業チェック（重要）
	    if ($vacation->staff->company_id !== $current->company_id) {
	        abort(403);
	    }

	    $vacation->update([
	        'status' => 'cancelled'
	    ]);

	    return back()->with('success', '休暇を取り消しました');
	}
}