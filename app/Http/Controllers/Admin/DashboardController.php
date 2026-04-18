<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyApplication;
use App\Models\Inquiry;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $openInquiryCount = Inquiry::where('status', 'open')->count();
        $answeredInquiryCount = Inquiry::where('status', 'answered')->count();
        $latestOpenInquiries = Inquiry::with('company')
            ->where('status', 'open')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'companyCount'         => Company::count(),
            'pendingCount'         => CompanyApplication::where('status', 'pending')->count(),
            'inactiveCount'        => Company::where('is_active', false)->count(),
            'openInquiryCount'     => $openInquiryCount,
            'answeredInquiryCount' => $answeredInquiryCount,
            'latestOpenInquiries'  => $latestOpenInquiries,
        ]);
    }
}