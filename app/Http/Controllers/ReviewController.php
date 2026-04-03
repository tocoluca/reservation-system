<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReviewController extends Controller
{
    public function create(string $token)
    {
        $reservation = Reservation::with(['company', 'customer', 'review'])
            ->where('review_token', $token)
            ->firstOrFail();

        if (!$this->canReview($reservation)) {
            abort(403, 'この予約は口コミ投稿できません。');
        }

        if ($reservation->review) {
            return redirect()->route('reviews.complete', $token);
        }

        return view('reviews.create', compact('reservation'));
    }

    public function store(Request $request, string $token)
    {
        $reservation = Reservation::with(['company', 'customer', 'review'])
            ->where('review_token', $token)
            ->firstOrFail();

        if (!$this->canReview($reservation)) {
            abort(403, 'この予約は口コミ投稿できません。');
        }

        if ($reservation->review) {
            return redirect()->route('reviews.complete', $token);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'nickname' => ['nullable', 'string', 'max:100'],
        ], [
            'rating.required' => '評価を選択してください。',
            'rating.min' => '評価は1〜5で選択してください。',
            'rating.max' => '評価は1〜5で選択してください。',
            'comment.max' => '口コミ本文は2000文字以内で入力してください。',
            'nickname.max' => 'ニックネームは100文字以内で入力してください。',
        ]);

        Review::create([
            'company_id' => $reservation->company_id,
            'reservation_id' => $reservation->id,
            'customer_id' => $reservation->customer_id ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'nickname' => $validated['nickname'] ?? 'お客様',
            'is_public' => false,
            'status' => 'pending',
            'reviewed_at' => now(),
        ]);

        $reservation->update([
            'review_submitted_at' => now(),
        ]);

        return redirect()->route('reviews.complete', $token);
    }

    public function complete(string $token)
    {
        $reservation = Reservation::where('review_token', $token)->firstOrFail();

        return view('reviews.complete', compact('reservation'));
    }

    private function canReview(Reservation $reservation): bool
    {
        if (!$reservation->review_token) {
            return false;
        }

        if ($reservation->status === 'cancelled') {
            return false;
        }

        if (!$reservation->start_at) {
            return false;
        }

        // 来店予定日時を過ぎた予約のみ投稿可
        if (Carbon::parse($reservation->start_at)->isFuture()) {
            return false;
        }

        return true;
    }
}