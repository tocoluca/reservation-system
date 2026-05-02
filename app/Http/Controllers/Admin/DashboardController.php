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
        $attentionStatuses = ['past_due', 'unpaid', 'incomplete', 'incomplete_expired'];
        $attentionCompanies = Company::query()
            ->withCount(['staff', 'reservations', 'customers'])
            ->where(function ($query) use ($attentionStatuses) {
                $query->where('is_active', false)
                    ->orWhere('is_initialized', false)
                    ->orWhere(function ($billingQuery) use ($attentionStatuses) {
                        $billingQuery->where(function ($campaignQuery) {
                            $campaignQuery->whereNull('billing_starts_at')
                                ->orWhere('billing_starts_at', '<=', now());
                        })
                            ->where(function ($statusQuery) use ($attentionStatuses) {
                                $statusQuery->where('is_billing_active', false)
                                    ->orWhereIn('subscription_status', $attentionStatuses);
                            });
                    });
            })
            ->latest()
            ->limit(5)
            ->get();
        $latestCompanies = Company::query()
            ->withCount(['staff', 'reservations', 'customers'])
            ->latest()
            ->limit(8)
            ->get();
        $billingCampaignCompanies = Company::query()
            ->whereNotNull('billing_starts_at')
            ->where('billing_starts_at', '>', now())
            ->orderBy('billing_starts_at')
            ->limit(5)
            ->get();
        $companyOptions = Company::query()
            ->orderBy('name')
            ->get(['id', 'name', 'company_code', 'billing_starts_at']);

        return view('admin.dashboard', [
            'companyCount'         => Company::count(),
            'pendingCount'         => CompanyApplication::where('status', 'pending')->count(),
            'inactiveCount'        => Company::where('is_active', false)->count(),
            'uninitializedCount'   => Company::where('is_initialized', false)->count(),
            'billingAttentionCount' => Company::where(function ($query) use ($attentionStatuses) {
                $query->where(function ($campaignQuery) {
                    $campaignQuery->whereNull('billing_starts_at')
                        ->orWhere('billing_starts_at', '<=', now());
                })
                    ->where(function ($statusQuery) use ($attentionStatuses) {
                        $statusQuery->where('is_billing_active', false)
                            ->orWhereIn('subscription_status', $attentionStatuses);
                    });
            })->count(),
            'attentionCompanies'   => $attentionCompanies,
            'latestCompanies'      => $latestCompanies,
            'billingCampaignCompanies' => $billingCampaignCompanies,
            'companyOptions'       => $companyOptions,
            'openInquiryCount'     => $openInquiryCount,
            'answeredInquiryCount' => $answeredInquiryCount,
            'latestOpenInquiries'  => $latestOpenInquiries,
        ]);
    }
}
