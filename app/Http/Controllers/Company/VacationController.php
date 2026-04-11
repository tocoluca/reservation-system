<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use App\Services\ReservationChangeNoticeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VacationController extends Controller
{
    public function __construct(
        protected ReservationChangeNoticeService $changeNoticeService
    ) {
    }

    private function authorizeVacationCard(): void
    {
        $staff = Auth::guard('company')->user();

        if (!$staff || !$staff->canDashboard('card.vacation')) {
            abort(403, '権限がありません');
        }
    }

    private function authorizeVacationApproval(): void
    {
        $staff = Auth::guard('company')->user();

        // 承認・却下・取消はまず master のみに絞る
        if (!$staff || !$staff->isMaster()) {
            abort(403, '承認権限がありません');
        }
    }

    public function index()
    {
        $this->authorizeVacationCard();

        $current = Auth::guard('company')->user();

        $vacations = Vacation::whereHas('staff', function ($q) use ($current) {
            $q->where('company_id', $current->company_id);
        })->latest()->get();

        return view('company.vacation.index', compact('vacations', 'current'));
    }

    public function create()
    {
        $this->authorizeVacationCard();

        $staff = auth()->guard('company')->user();

        return view('company.vacation.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $this->authorizeVacationCard();

        $staff = auth()->guard('company')->user();
        $isFullDay = $request->boolean('is_full_day');

        if ($isFullDay) {
            $request->validate([
                'vacation_date' => 'required|date',
            ]);

            $start = Carbon::parse($request->vacation_date)->startOfDay();
            $end = Carbon::parse($request->vacation_date)->endOfDay();
        } else {
            $request->validate([
                'start_date' => 'required|date',
                'start_time' => 'required',
                'end_date' => 'required|date',
                'end_time' => 'required',
            ]);

            $start = Carbon::parse($request->start_date . ' ' . $request->start_time);
            $end = Carbon::parse($request->end_date . ' ' . $request->end_time);

            if ($start >= $end) {
                return back()->withErrors([
                    'end_time' => '終了は開始より後にしてください',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($staff, $start, $end, $isFullDay) {
            Vacation::create([
                'staff_id' => $staff->id,
                'start_at' => $start,
                'end_at' => $end,
                'status' => 'pending',
                'is_full_day' => $isFullDay,
            ]);
        });

        return redirect()->route('company.vacation.index')
            ->with('success', '休暇申請を送信しました');
    }

    public function approve(Vacation $vacation)
    {
        $this->authorizeVacationApproval();

        $current = auth()->guard('company')->user();

        if ($vacation->staff->company_id !== $current->company_id) {
            abort(403);
        }

        $vacation->update([
            'status' => 'approved',
        ]);

        $this->changeNoticeService->createForStaffVacation(
            company: $current->company,
            staff: $vacation->staff,
            startAt: Carbon::parse($vacation->start_at),
            endAt: Carbon::parse($vacation->end_at),
            reasonText: $vacation->staff->name . ' の休暇承認により、ご予約内容の変更をお願いしております。'
        );

        return back()->with('success', '承認しました');
    }

    public function reject(Vacation $vacation)
    {
        $this->authorizeVacationApproval();

        $current = auth()->guard('company')->user();

        if ($vacation->staff->company_id !== $current->company_id) {
            abort(403);
        }

        $vacation->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', '却下しました');
    }

    public function destroy(Vacation $vacation)
    {
        $this->authorizeVacationCard();

        $current = Auth::guard('company')->user();

        if ($vacation->staff_id !== $current->id && !$current->isMaster()) {
            abort(403);
        }

        if ($vacation->staff->company_id !== $current->company_id) {
            abort(403);
        }

        $vacation->delete();

        return back()->with('success', '削除しました');
    }

    public function cancel(Vacation $vacation)
    {
        $this->authorizeVacationApproval();

        $current = auth()->guard('company')->user();

        if ($vacation->status !== 'approved') {
            return back()->withErrors([
                'status' => '承認済の休暇のみ取消できます',
            ]);
        }

        if ($vacation->staff->company_id !== $current->company_id) {
            abort(403);
        }

        $vacation->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', '休暇を取り消しました');
    }
}