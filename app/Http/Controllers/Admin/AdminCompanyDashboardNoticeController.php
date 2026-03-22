<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDashboardNotice;
use Illuminate\Http\Request;
//画像圧縮
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdminCompanyDashboardNoticeController extends Controller
{
    public function index()
    {
        $notices = CompanyDashboardNotice::with('company')
            ->orderByDesc('is_important')
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.company_dashboard_notices.index', compact('notices'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();

        return view('admin.company_dashboard_notices.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'is_new'       => ['nullable', 'boolean'],
            'is_important' => ['nullable', 'boolean'],
            'is_active'    => ['nullable', 'boolean'],
            'target_type'  => ['required', 'in:all,company'],
            'company_id'   => ['nullable', 'exists:companies,id'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
        ], [
            'title.required' => '題名を入力してください。',
            'title.max' => '題名は255文字以内で入力してください。',
            'image.image' => '画像ファイルを選択してください。',
            'image.max' => '画像は2MB以内にしてください。',
            'company_id.exists' => '対象企業が存在しません。',
            'end_date.after_or_equal' => '表示終了日は開始日以降を指定してください。',
        ]);

        if ($request->hasFile('image')) {

		    $file = $request->file('image');

			$manager = new ImageManager(new Driver());
			$image = $manager->read($file);

			// 最大800pxに縮小
			$image->scaleDown(width: 800);

			// WebP変換
			$encoded = $image->toWebp(quality: 85);


		    $dir = public_path('admins/notices');

		    if (!file_exists($dir)) {
		        mkdir($dir, 0755, true);
		    }

		    $filename = uniqid().'.webp';

		    $file->move($dir, $filename);

		    $path = 'admins/notices/'.$filename;

		    //画像の保存
		    file_put_contents($path, $encoded);

            $data['image'] = $path;

        }

        $data['is_new'] = $request->boolean('is_new');
        $data['is_important'] = $request->boolean('is_important');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['target_type'] === 'all') {
            $data['company_id'] = null;
        }

        CompanyDashboardNotice::create($data);

        return redirect()
            ->route('admin.company-dashboard-notices.index')
            ->with('success', 'お知らせを登録しました。');
    }

    public function edit(CompanyDashboardNotice $companyDashboardNotice)
    {
        $companies = Company::orderBy('name')->get();

        return view('admin.company_dashboard_notices.edit', [
            'notice' => $companyDashboardNotice,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, CompanyDashboardNotice $companyDashboardNotice)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'is_new'       => ['nullable', 'boolean'],
            'is_important' => ['nullable', 'boolean'],
            'is_active'    => ['nullable', 'boolean'],
            'target_type'  => ['required', 'in:all,company'],
            'company_id'   => ['nullable', 'exists:companies,id'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
        ], [
            'title.required' => '題名を入力してください。',
            'title.max' => '題名は255文字以内で入力してください。',
            'image.image' => '画像ファイルを選択してください。',
            'image.max' => '画像は2MB以内にしてください。',
            'company_id.exists' => '対象企業が存在しません。',
            'end_date.after_or_equal' => '表示終了日は開始日以降を指定してください。',
        ]);

        if ($request->hasFile('image')) {
            if ($companyDashboardNotice->image && file_exists(public_path($companyDashboardNotice->image))) {
				    unlink(public_path($companyDashboardNotice->image));
            }

		    $file = $request->file('image');

			$manager = new ImageManager(new Driver());
			$image = $manager->read($file);

			// 最大800pxに縮小
			$image->scaleDown(width: 800);

			// WebP変換
			$encoded = $image->toWebp(quality: 85);


		    $dir = public_path('admins/notices');

		    if (!file_exists($dir)) {
		        mkdir($dir, 0755, true);
		    }

		    $filename = uniqid().'.webp';

		    $file->move($dir, $filename);

		    $path = 'admins/notices/'.$filename;

		    //画像の保存
		    file_put_contents($path, $encoded);

            $data['image'] = $path;

        }

        $data['is_new'] = $request->boolean('is_new');
        $data['is_important'] = $request->boolean('is_important');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['target_type'] === 'all') {
            $data['company_id'] = null;
        }

        $companyDashboardNotice->update($data);

        return redirect()
            ->route('admin.company-dashboard-notices.index')
            ->with('success', 'お知らせを更新しました。');
    }

    public function destroy(CompanyDashboardNotice $companyDashboardNotice)
    {

		//画像削除
		$oldImagePath = $companyDashboardNotice->image;

		if ($oldImagePath && file_exists(public_path($oldImagePath))) {
		    unlink(public_path($oldImagePath));
		}

        $companyDashboardNotice->delete();

        return redirect()
            ->route('admin.company-dashboard-notices.index')
            ->with('success', 'お知らせを削除しました。');
    }
}