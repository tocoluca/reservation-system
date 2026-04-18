<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        $company = auth()->guard('company')->user()->company;

        $inquiries = Inquiry::where('company_id', $company->id)
            ->latest()
            ->paginate(10);

        return view('company.support.index', compact('company', 'inquiries'));
    }

    public function store(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'subject'  => ['required', 'string', 'max:255'],
            'body'     => ['required', 'string', 'max:5000'],
        ]);

        Inquiry::create([
            'company_id' => $company->id,
            'category'   => $data['category'] ?? null,
            'subject'    => $data['subject'],
            'body'       => $data['body'],
            'status'     => 'open',
        ]);

        return redirect()
            ->route('company.support.index')
            ->with('success', 'お問い合わせを受け付けました。回答までしばらくお待ちください。');
    }

    public function show(Inquiry $inquiry)
    {
        $company = auth()->guard('company')->user()->company;

        abort_unless($inquiry->company_id === $company->id, 403);

        if ($inquiry->status === 'answered' && ! $inquiry->is_read_by_company) {
            $inquiry->update([
                'is_read_by_company' => true,
                'company_read_at' => now(),
            ]);
        }

        return view('company.support.show', compact('company', 'inquiry'));
    }
}