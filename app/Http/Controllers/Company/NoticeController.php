<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
//画像圧縮
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class NoticeController extends Controller
{
    public function index()
    {
        $company = auth()->guard('company')->user()->company;

        $notices = Notice::where('company_id', $company->id)
            ->latest()
            ->get();

        return view('company.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('company.notices.create');
    }

    public function store(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'nullable',
            'image' => 'nullable|image|max:5000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);


	$path = null;
    // 画像保存

	if ($request->hasFile('image')) {

	    $file = $request->file('image');

		$manager = new ImageManager(new Driver());
		$image = $manager->read($file);

		// 最大800pxに縮小
		$image->scaleDown(width: 800);

		// WebP変換
		$encoded = $image->toWebp(quality: 85);


	    $dir = public_path('companies/'.$company->id.'/notices');

	    if (!file_exists($dir)) {
	        mkdir($dir, 0755, true);
	    }

	    $filename = uniqid().'.webp';

	    $file->move($dir, $filename);

	    $path = 'companies/'.$company->id.'/notices/'.$filename;

	    //画像の保存
	    file_put_contents($path, $encoded);

	}


        $data['company_id'] = $company->id;
        $data['is_important'] = $request->boolean('is_important');
	    //画像パスの保存
        $data['image'] = $path;

        Notice::create($data);

        return redirect()->route('company.notices.index')
            ->with('success', '登録しました');
    }

    public function edit(Notice $notice)
    {
        return view('company.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {

	    //画像のフォルダに必要
        $company = auth()->guard('company')->user()->company;

        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'nullable',
            'image' => 'nullable|image|max:5000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

		$oldImagePath = $notice->image;

        if ($request->hasFile('image')) {

		    $file = $request->file('image');

			$manager = new ImageManager(new Driver());
			$image = $manager->read($file);

			// 最大800pxに縮小
			$image->scaleDown(width: 800);

			// WebP変換
			$encoded = $image->toWebp(quality: 85);


		    $dir = public_path('companies/'.$company->id.'/notices');

		    if (!file_exists($dir)) {
		        mkdir($dir, 0755, true);
		    }

		    $filename = uniqid().'.webp';

		    $file->move($dir, $filename);

		    $path = 'companies/'.$company->id.'/notices/'.$filename;

		    file_put_contents($path, $encoded);

			$data['image'] = $path;

			// 削除
			if ($request->hasFile('image') && $oldImagePath) {
				if ($oldImagePath && file_exists(public_path($oldImagePath))) {
				    unlink(public_path($oldImagePath));
				}
			}

             //$request->file('image')->store('notices', 'public');
        }

        $data['is_important'] = $request->boolean('is_important');

        $notice->update($data);

        return redirect()->route('company.notices.index')
            ->with('success', '更新しました');
    }

    public function destroy(Notice $notice)
    {

		//画像削除
		$oldImagePath = $notice->image;

		if ($oldImagePath && file_exists(public_path($oldImagePath))) {
		    unlink(public_path($oldImagePath));
		}

        $notice->delete();

        return redirect()->back()->with('success', '削除しました');
    }
}
