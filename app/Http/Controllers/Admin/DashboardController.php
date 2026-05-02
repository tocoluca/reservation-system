<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyApplication;
use App\Models\Inquiry;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $hasBillingStartsAt = Schema::hasColumn('companies', 'billing_starts_at');
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
            ->where(function ($query) use ($attentionStatuses, $hasBillingStartsAt) {
                $query->where('is_active', false)
                    ->orWhere('is_initialized', false)
                    ->orWhere(function ($billingQuery) use ($attentionStatuses, $hasBillingStartsAt) {
                        if ($hasBillingStartsAt) {
                            $billingQuery->where(function ($campaignQuery) {
                                $campaignQuery->whereNull('billing_starts_at')
                                    ->orWhere('billing_starts_at', '<=', now());
                            });
                        }

                        $billingQuery->where(function ($statusQuery) use ($attentionStatuses) {
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
        $billingCampaignCompanies = $hasBillingStartsAt
            ? Company::query()
                ->whereNotNull('billing_starts_at')
                ->where('billing_starts_at', '>', now())
                ->orderBy('billing_starts_at')
                ->limit(5)
                ->get()
            : collect();
        $companyOptionColumns = ['id', 'name', 'company_code'];
        if ($hasBillingStartsAt) {
            $companyOptionColumns[] = 'billing_starts_at';
        }

        $companyOptions = Company::query()
            ->orderBy('name')
            ->get($companyOptionColumns);

        return view('admin.dashboard', [
            'companyCount'         => Company::count(),
            'pendingCount'         => CompanyApplication::where('status', 'pending')->count(),
            'inactiveCount'        => Company::where('is_active', false)->count(),
            'uninitializedCount'   => Company::where('is_initialized', false)->count(),
            'billingAttentionCount' => Company::where(function ($query) use ($attentionStatuses, $hasBillingStartsAt) {
                if ($hasBillingStartsAt) {
                    $query->where(function ($campaignQuery) {
                        $campaignQuery->whereNull('billing_starts_at')
                            ->orWhere('billing_starts_at', '<=', now());
                    });
                }

                $query->where(function ($statusQuery) use ($attentionStatuses) {
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
            'billingStartCampaignEnabled' => $hasBillingStartsAt,
        ]);
    }
}
