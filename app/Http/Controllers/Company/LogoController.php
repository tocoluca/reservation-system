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

	// 最大600pxに縮小（比率維持）
	$image->scaleDown(width: 600, height: 600);

    // WebP変換
    $encoded = $image->toWebp(quality: 85);

	$filename = uniqid().'.webp';

    // 👇 ここが重要
    $dir = public_path('companies/'.$company->id.'/logos');

    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    $path = 'companies/'.$company->id.'/logos/'.$filename;

    //画像の保存
    file_put_contents($path, $encoded);

    // 旧ロゴ削除
    if (!empty($company->logo)) {
        $oldPath = public_path($company->logo);

        if (is_file($oldPath) && file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    $company->update([
        'logo_path' => $path
    ]);

    return back()->with('success', 'ロゴを更新しました');
}


}