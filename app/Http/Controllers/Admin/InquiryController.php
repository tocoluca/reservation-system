<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inquiry::with('company')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $inquiries = $query->paginate(20)->withQueryString();

        $statusCounts = Inquiry::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'all' => (int) $statusCounts->sum(),
            'open' => (int) ($statusCounts['open'] ?? 0),
            'answered' => (int) ($statusCounts['answered'] ?? 0),
            'closed' => (int) ($statusCounts['closed'] ?? 0),
        ];

        return view('admin.inquiries.index', compact('inquiries', 'stats'));
    }

    public function show(Inquiry $inquiry)
    {
        $inquiry->load('company', 'repliedAdmin');

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function reply(Request $request, Inquiry $inquiry)
    {
        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'max:10000'],
            'status' => ['nullable', 'string', 'in:answered,closed'],
        ]);

        $inquiry->update([
            'admin_reply' => $data['admin_reply'],
            'status' => $data['status'] ?? 'answered',
            'replied_at' => now(),
            'replied_admin_id' => auth('admin')->id(),
            'is_read_by_company' => false,
            'company_read_at' => null,
        ]);

        return redirect()
            ->route('admin.inquiries.show', $inquiry)
            ->with('success', '回答を登録しました。');
    }
}
