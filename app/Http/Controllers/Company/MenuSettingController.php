<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MenuSettingController extends Controller
{
    public function index()
    {

        $company = auth()->guard('company')->user()->company;

        $categories = MenuCategory::where('company_id', $company->id)->get();

        $tags = MenuTag::where('company_id', $company->id)->get();

        return view('company.menu.settings', compact(
            'categories',
            'tags'
        ));

    }

    public function storeCategory(Request $request)
    {

        $company = auth()->guard('company')->user()->company;

        $request->validate([
            'name' => 'required|max:50',
        ]);

        MenuCategory::create([
            'company_id' => $company->id,
            'name' => $request->name,
        ]);

        return back();

    }

    public function storeTag(Request $request)
    {

        $company = auth()->guard('company')->user()->company;

        $request->validate([
            'name' => 'required|max:50',
        ]);

        MenuTag::create([
            'company_id' => $company->id,
            'name' => $request->name,
        ]);

        return back();

    }

    public function updateCategoryImage(Request $request, $id)
    {
        $company = auth()->guard('company')->user()->company;

        $category = MenuCategory::where('company_id', $company->id)
            ->findOrFail($id);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'image.required' => 'カテゴリー画像を選択してください。',
            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '画像は JPG・PNG・WebP 形式に対応しています。',
            'image.max' => '画像は10MB以内でアップロードしてください。',
        ]);

        $relativeDirectory = "companies/{$company->id}/menu-categories";
        $directory = public_path($relativeDirectory);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid().'.webp';
        $relativePath = $relativeDirectory.'/'.$filename;

        $manager = new ImageManager(new Driver);
        $manager->read($request->file('image'))
            ->orient()
            ->cover(640, 640)
            ->toWebp(quality: 85)
            ->save(public_path($relativePath));

        $oldImagePath = $category->image_path;
        $category->update(['image_path' => $relativePath]);
        $this->deleteImageFile($oldImagePath, $company->id);

        return back()->with('success', "「{$category->name}」の画像を更新しました。");
    }

    public function deleteCategoryImage($id)
    {
        $company = auth()->guard('company')->user()->company;

        $category = MenuCategory::where('company_id', $company->id)
            ->findOrFail($id);

        $oldImagePath = $category->image_path;
        $category->update(['image_path' => null]);
        $this->deleteImageFile($oldImagePath, $company->id);

        return back()->with('success', "「{$category->name}」を標準画像に戻しました。");
    }

    private function deleteImageFile(?string $path, int $companyId): void
    {
        $expectedPrefix = "companies/{$companyId}/menu-categories/";

        if (! $path || ! str_starts_with($path, $expectedPrefix)) {
            return;
        }

        $fullPath = public_path($path);

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    public function deleteCategory($id)
    {

        $company = auth()->guard('company')->user()->company;

        $category = MenuCategory::where('company_id', $company->id)
            ->with('menus')
            ->where('id', $id)
            ->firstOrFail();

        if ($category->menus->count() > 0) {

            $menuNames = $category->menus->pluck('name')->implode('、');

            return back()->with(
                'error',
                'このカテゴリーは次のメニューで使用されています：'.$menuNames
            );

        }

        $imagePath = $category->image_path;
        $category->delete();
        $this->deleteImageFile($imagePath, $company->id);

        return back()->with('success', 'カテゴリーを削除しました');

    }

    public function deleteTag($id)
    {

        $company = auth()->guard('company')->user()->company;

        $tag = MenuTag::where('company_id', $company->id)
            ->findOrFail($id);

        $tag->menus()->detach();

        $tag->delete();

        return back();

    }
}
