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

    // 中央正方形トリミング
    $size = min($image->width(), $image->height());
    $image->crop($size, $size);

    // 600x600
    $image->resize(600, 600);

    // WebP
    $encoded = $image->toWebp(quality: 85);

    $filename = $company->company_code . '.webp';
    $path = public_path('logos');

    // フォルダなければ作成
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }

    // 保存
    file_put_contents($path . '/' . $filename, $encoded);

    $company->update([
        'logo_path' => 'logos/' . $filename
    ]);

    return back()->with('success', 'ロゴを更新しました');
}