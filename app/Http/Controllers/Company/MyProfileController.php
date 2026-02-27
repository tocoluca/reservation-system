<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'comment'  => 'nullable|string|max:500',
            'password' => 'nullable|min:8|confirmed',
            'image'    => 'nullable|image|max:2048'
        ]);

        /*
        |--------------------------------------------------------------------------
        | 画像アップロード（public直保存・ロリポップ対応）
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            // 既存画像削除（public直保存版）
            if ($staff->image_path) {
                $oldPath = public_path($staff->image_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $manager = new ImageManager(new Driver());
            $image   = $manager->read($request->file('image'));

            // 横幅最大400pxに縮小（縦横比維持）
            $image->scaleDown(width: 400);

            $filename = uniqid() . '.webp';

            // 保存先ディレクトリ
            $relativePath = "uploads/companies/{$companyId}/staff";
            $savePath     = public_path($relativePath);

            // フォルダがなければ作成
            if (!file_exists($savePath)) {
                mkdir($savePath, 0777, true);
            }

            // WebPで保存（品質80）
            $image->toWebp(80)->save($savePath . '/' . $filename);

            // DBには public からの相対パスを保存
            $staff->image_path = $relativePath . '/' . $filename;
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

        return back()->with('success', 'プロフィールを更新しました');
    }
}