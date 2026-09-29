<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdateReservationHeroRequest;
use App\Services\ReservationHeroImageProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ReservationHeroController extends Controller
{
    public function edit()
    {
        $staff = Auth::guard('company')->user();
        abort_if(! $staff || ! $staff->canDashboard('card.company_info'), 403);

        return view('company.reservation_hero', ['company' => $staff->company]);
    }

    public function update(
        UpdateReservationHeroRequest $request,
        ReservationHeroImageProcessor $images
    ): RedirectResponse {
        $company = $request->user('company')->company;
        $validated = $request->validated();
        $oldPath = $company->reservation_hero_image_path;
        $newPath = null;

        try {
            if ($request->hasFile('hero_image')) {
                $newPath = $images->store($request->file('hero_image'), (int) $company->id);
            }

            $company->update([
                'reservation_hero_image_path' => $newPath ?? $oldPath,
                'reservation_hero_heading' => $validated['reservation_hero_heading'] ?? null,
                'reservation_hero_subheading' => $validated['reservation_hero_subheading'] ?? null,
                'reservation_hero_heading_size' => $validated['reservation_hero_heading_size'],
                'reservation_hero_subheading_size' => $validated['reservation_hero_subheading_size'],
                'reservation_hero_text_color' => strtolower($validated['reservation_hero_text_color']),
            ]);
        } catch (Throwable $e) {
            if ($newPath) {
                $images->delete($newPath, (int) $company->id);
            }

            throw $e;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) {
            $images->delete($oldPath, (int) $company->id);
        }

        return back()->with('success', '予約画面設定を更新しました。');
    }

    public function destroyImage(ReservationHeroImageProcessor $images): RedirectResponse
    {
        $staff = Auth::guard('company')->user();
        abort_if(! $staff || ! $staff->canDashboard('card.company_info'), 403);

        $company = $staff->company;
        $path = $company->reservation_hero_image_path;

        $company->update(['reservation_hero_image_path' => null]);
        $images->delete($path, (int) $company->id);

        return back()->with('success', 'メイン画像を削除しました。');
    }
}
