<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MyProfileController extends Controller
{
    public function edit()
    {
        $staff = Auth::guard('company')->user();
        return view('company.my-profile', compact('staff'));
    }

    public function update(Request $request)
    {
        $staff = Auth::guard('company')->user();
        $companyId = $staff->company_id;

        $request->validate([
            'comment' => 'nullable|string|max:500',
            'password' => 'nullable|min:8|confirmed',
            'image' => 'nullable|image|max:2048'
        ]);

        /*
        |--------------------------------------------------------------------------
        | 画像アップロード（企業別フォルダ）
        |--------------------------------------------------------------------------
        */

	if ($request->hasFile('image')) {

	    if ($staff->image_path &&
	        Storage::disk('public')->exists($staff->image_path)) {
	        Storage::disk('public')->delete($staff->image_path);
	    }

	    $companyId = $staff->company_id;
	    $folderPath = "companies/{$companyId}/staff";

	    $manager = new ImageManager(new Driver());

	    $image = $manager->read($request->file('image'));

	    // 横幅最大400pxにリサイズ（縦横比維持）
	    $image->scaleDown(width: 400);

	    // WebPで圧縮保存（品質80）
	    $filename = uniqid().'.webp';

	    Storage::disk('public')->put(
	        $folderPath.'/'.$filename,
	        $image->toWebp(80)
	    );

	    $staff->image_path = $folderPath.'/'.$filename;
	}
	        /*
        |--------------------------------------------------------------------------
        | コメント更新
        |--------------------------------------------------------------------------
        */

        $staff->comment = $request->comment;

        /*
        |--------------------------------------------------------------------------
        | パスワード更新
        |--------------------------------------------------------------------------
        */

        if ($request->filled('password')) {
            $staff->password = Hash::make($request->password);
            $staff->force_password_change = false;
        }

        $staff->save();

        return back()->with('success','プロフィールを更新しました');
    }
}