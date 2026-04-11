<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class LogoController extends Controller
{
    public function edit()
    {
        $staff = Auth::guard('company')->user();

        if (!$staff || !$staff->canDashboard('card.logo')) {
            abort(403, '権限がありません');
        }

        return view('company.logo', [
            'company' => $staff->company,
        ]);
    }

    public function update(Request $request)
    {
        $staff = Auth::guard('company')->user();

        if (!$staff || !$staff->canDashboard('card.logo')) {
            abort(403, '権限がありません');
        }

        $request->validate([
            'logo' => 'required|image|max:4096',
        ]);

        $company = $staff->company;
        $file = $request->file('logo');

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);
        $image->scaleDown(width: 600, height: 600);
        $encoded = $image->toWebp(quality: 85);

        $filename = uniqid() . '.webp';
        $dir = public_path('companies/' . $company->id . '/logos');

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = 'companies/' . $company->id . '/logos/' . $filename;
        file_put_contents(public_path($path), $encoded);

        if (!empty($company->logo_path)) {
            $oldPath = public_path($company->logo_path);

            if (is_file($oldPath) && file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $company->update([
            'logo_path' => $path,
        ]);

        return back()->with('success', 'ロゴを更新しました');
    }
}