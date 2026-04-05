<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $company = auth()->guard('company')->user()->company;
        $this->ensureReviewEnabled($company);

        $query = Review::with(['reservation', 'customer'])
            ->where('company_id', $company->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reviews = $query->paginate(20);

        return view('company.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $company = auth()->guard('company')->user()->company;
        $this->ensureReviewEnabled($company);

        $this->authorizeCompanyReview($review, $company->id);

        $review->load(['reservation', 'customer', 'company']);

        return view('company.reviews.show', compact('review'));
    }

    public function approve(Review $review)
    {
        $company = auth()->guard('company')->user()->company;
        $this->ensureReviewEnabled($company);

        $this->authorizeCompanyReview($review, $company->id);

        $review->update([
            'status' => 'approved',
            'is_public' => true,
        ]);

        return back()->with('success', '口コミを公開しました。');
    }

    public function reject(Review $review)
    {
        $company = auth()->guard('company')->user()->company;
        $this->ensureReviewEnabled($company);

        $this->authorizeCompanyReview($review, $company->id);

        $review->update([
            'status' => 'rejected',
            'is_public' => false,
        ]);

        return back()->with('success', '口コミを非公開にしました。');
    }

    public function reply(Request $request, Review $review)
    {
        $company = auth()->guard('company')->user()->company;
        $this->ensureReviewEnabled($company);

        $this->authorizeCompanyReview($review, $company->id);

        $validated = $request->validate([
            'owner_reply' => ['nullable', 'string', 'max:2000'],
        ], [
            'owner_reply.max' => '返信は2000文字以内で入力してください。',
        ]);

        $review->update([
            'owner_reply' => $validated['owner_reply'] ?? null,
            'owner_replied_at' => filled($validated['owner_reply'] ?? null) ? now() : null,
        ]);

        return back()->with('success', '返信を保存しました。');
    }

    private function authorizeCompanyReview(Review $review, int $companyId): void
    {
        abort_if($review->company_id !== $companyId, 403);
    }

    private function ensureReviewEnabled($company): void
    {
        abort_unless((bool)($company->review_enabled ?? false), 404);
    }
}