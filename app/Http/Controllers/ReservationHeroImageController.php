<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\ReservationHeroImageProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReservationHeroImageController extends Controller
{
    public function show(string $company_code, ReservationHeroImageProcessor $images): BinaryFileResponse
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();
        $path = $images->absolutePath($company->reservation_hero_image_path, (int) $company->id);

        abort_unless($path, 404);

        return response()->file($path, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
