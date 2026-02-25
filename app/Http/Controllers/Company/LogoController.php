<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class LogoController extends Controller
{
    public function edit()
    {
        $staff = Auth::guard('company')->user();

        if ($staff->role !== 'master') {
            abort(403);
        }

        return view('company.logo', [
            'company' => $staff->company
        ]);
    }

    public function update(Request $request)
    {
        $staff = Auth::guard('company')->user();

        if ($staff->role !== 'master') {
            abort(403);
        }

        $request->validate([
            'logo' => 'required|image|max:4096'
        ]);

        $company = $staff->company;

        $file = $request->file('logo');

        $manager = new ImageManager(new Driver());

        $image = $manager->read($file);

        // 🔥 中央正方形トリミング
        $size = min($image->width(), $image->height());
        $image->crop($size, $size);

        // 🔥 600x600にリサイズ
        $image->resize(600, 600);

        // 🔥 WebPに変換（透過保持）
        $encoded = $image->toWebp(quality: 85);

        // 🔥 company_code固定ファイル名
        $filename = 'logos/' . $company->company_code . '.webp';

        // 既存削除
        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }

        Storage::disk('public')->put($filename, $encoded);

        $company->update([
            'logo_path' => $filename
        ]);

        return back()->with('success', 'ロゴを更新しました');
    }
}