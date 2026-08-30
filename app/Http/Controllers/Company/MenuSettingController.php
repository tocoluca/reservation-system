<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MenuSettingController extends Controller
{
    public function index()
    {

        $company = auth()->guard('company')->user()->company;

        $categories = MenuCategory::where('company_id', $company->id)
            ->withCount('menus')
            ->orderBy('id')
            ->get();

        $tags = MenuTag::where('company_id', $company->id)
            ->withCount('menus')
            ->orderBy('name')
            ->get();

        return view('company.menu.settings', compact(
            'categories',
            'tags'
        ));

    }

    public function storeCategory(Request $request)
    {

        $company = auth()->guard('company')->user()->company;

        $request->merge(['category_name' => trim((string) $request->input('category_name'))]);

        $validated = $request->validate([
            'category_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('menu_categories', 'name')->where('company_id', $company->id),
            ],
        ], [
            'category_name.required' => 'カテゴリー名を入力してください。',
            'category_name.max' => 'カテゴリー名は50文字以内で入力してください。',
            'category_name.unique' => '同じ名前のカテゴリーがすでに登録されています。',
        ]);

        MenuCategory::create([
            'company_id' => $company->id,
            'name' => $validated['category_name'],
        ]);

        return back()->with('success', 'カテゴリーを追加しました。');

    }

    public function storeTag(Request $request)
    {

        $company = auth()->guard('company')->user()->company;

        $request->merge(['tag_name' => trim((string) $request->input('tag_name'))]);

        $validated = $request->validate([
            'tag_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('menu_tags', 'name')->where('company_id', $company->id),
            ],
        ], [
            'tag_name.required' => 'タグ名を入力してください。',
            'tag_name.max' => 'タグ名は50文字以内で入力してください。',
            'tag_name.unique' => '同じ名前のタグがすでに登録されています。',
        ]);

        MenuTag::create([
            'company_id' => $company->id,
            'name' => $validated['tag_name'],
        ]);

        return back()->with('success', 'タグを追加しました。');

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

        return back()->with('success', 'タグを削除しました。');

    }
}
