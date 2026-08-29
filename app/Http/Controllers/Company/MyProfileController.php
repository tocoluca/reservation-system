<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\StaffProfileImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MyProfileController extends Controller
{
    public function edit()
    {
        $staff = Auth::guard('company')->user();
        abort_if(! $staff || ! $staff->canDashboard('card.my_profile'), 403);

        return view('company.my-profile', compact('staff'));
    }

    public function update(Request $request)
    {
        $staff = Auth::guard('company')->user();
        abort_if(! $staff || ! $staff->canDashboard('card.my_profile'), 403);

        if ($request->input('section') === 'password') {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'password.required' => '新しいパスワードを入力してください。',
                'password.min' => 'パスワードは8文字以上で入力してください。',
                'password.confirmed' => '新しいパスワードと確認用パスワードが一致しません。',
            ]);

            $staff->password = Hash::make($request->password);
            $staff->force_password_change = false;
            $staff->save();

            return back()->with('success', 'パスワードを更新しました。');
        }

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'comment.max' => 'コメントは500文字以内で入力してください。',
        ]);

        $staff->comment = $validated['comment'] ?? null;
        $staff->save();

        return back()->with('success', 'プロフィールコメントを更新しました。');
    }

    public function updateImage(Request $request, StaffProfileImageProcessor $imageProcessor)
    {
        $staff = Auth::guard('company')->user();
        abort_if(! $staff || ! $staff->canDashboard('card.my_profile'), 403);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'image.required' => 'プロフィール画像を選択してください。',
            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '画像は JPG・PNG・WebP 形式に対応しています。',
            'image.max' => '画像は10MB以内でアップロードしてください。',
        ]);

        $newImagePath = $imageProcessor->store($request->file('image'), $staff->company_id);
        $oldImagePath = $staff->image_path;

        $staff->image_path = $newImagePath;
        $staff->save();

        $this->deleteImageFile($oldImagePath, $staff->company_id);

        return back()->with('success', 'プロフィール画像を更新しました。');
    }

    public function deleteImage()
    {
        $staff = Auth::guard('company')->user();
        abort_if(! $staff || ! $staff->canDashboard('card.my_profile'), 403);

        $oldImagePath = $staff->image_path;
        $staff->image_path = null;
        $staff->save();

        $this->deleteImageFile($oldImagePath, $staff->company_id);

        return back()->with('success', 'プロフィール画像を削除しました。');
    }

    private function deleteImageFile(?string $path, int $companyId): void
    {
        $expectedPrefix = "companies/{$companyId}/staff/";

        if (! $path || ! str_starts_with($path, $expectedPrefix)) {
            return;
        }

        $fullPath = public_path($path);

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
