<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\StylePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class StylePostController extends Controller
{
    public function index()
    {
        $company = Auth::guard('company')->user()->company;

        $styles = StylePost::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(12);

        return view('company.style_posts.index', compact('company', 'styles'));
    }

    public function create()
    {
        $company = Auth::guard('company')->user()->company;
        return view('company.style_posts.create', compact('company'));
    }

    public function store(Request $request)
    {
        $company = Auth::guard('company')->user()->company;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'is_public' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'title.required' => 'タイトルを入力してください。',
            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '画像は jpg / jpeg / png / webp に対応しています。',
            'image.max' => '画像は2MB以内でアップロードしてください。',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {

            $manager = new ImageManager(new Driver());
            $image   = $manager->read($request->file('image'));

            // 横幅最大800pxに縮小（縦横比維持）
            $image->scaleDown(width: 800);

            $filename = uniqid() . '.webp';

            // 保存先ディレクトリ
            $relativePath = "companies/{$company->id}/style";
            $savePath     = public_path($relativePath);

            // フォルダがなければ作成
            if (!file_exists($savePath)) {
                mkdir($savePath, 0777, true);
            }

            // WebPで保存（品質80）
            $image->toWebp(80)->save($savePath . '/' . $filename);

            // DBには public からの相対パスを保存
            $imagePath = $relativePath . '/' . $filename;

        }

        StylePost::create([
            'company_id' => $company->id,
            'title' => $validated['title'],
            'comment' => $validated['comment'] ?? null,
            'image_path' => $imagePath,
            'is_public' => $request->boolean('is_public', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('company.style-posts.index')
            ->with('success', '最新スタイルを登録しました。');
    }

    public function edit($id)
    {
        $company = Auth::guard('company')->user()->company;

        $style = StylePost::where('company_id', $company->id)
            ->findOrFail($id);

        return view('company.style_posts.edit', compact('company', 'style'));
    }

    public function update(Request $request, $id)
    {
        $company = Auth::guard('company')->user()->company;

        $style = StylePost::where('company_id', $company->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'is_public' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $imagePath = $style->image_path;

        if ($request->hasFile('image') && $style->image_path) {
                $oldPath = public_path($style->image_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
        }

        if ($request->hasFile('image')) {

            $manager = new ImageManager(new Driver());
            $image   = $manager->read($request->file('image'));

            // 横幅最大800pxに縮小（縦横比維持）
            $image->scaleDown(width: 800);

            $filename = uniqid() . '.webp';

            // 保存先ディレクトリ
            $relativePath = "companies/{$style->company_id}/style";
            $savePath     = public_path($relativePath);

//Log::debug('LOG3');

            // フォルダがなければ作成
            if (!file_exists($savePath)) {
                mkdir($savePath, 0777, true);
            }

            // WebPで保存（品質80）
            $image->toWebp(80)->save($savePath . '/' . $filename);

            // DBには public からの相対パスを保存
            $imagePath = $relativePath . '/' . $filename;
//Log::debug('LOG4'.$relativePath.'/'.$filename);

        }

        $style->update([
            'title' => $validated['title'],
            'comment' => $validated['comment'] ?? null,
            'image_path' => $imagePath,
            'is_public' => $request->boolean('is_public', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('company.style-posts.index')
            ->with('success', '最新スタイルを更新しました。');
    }

    public function destroy($id)
    {
        $company = Auth::guard('company')->user()->company;

        $style = StylePost::where('company_id', $company->id)
            ->findOrFail($id);

        if ($style->image_path) {
                $oldPath = public_path($style->image_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
        }

        $style->delete();

        return redirect()
            ->route('company.style-posts.index')
            ->with('success', '最新スタイルを削除しました。');
    }
}