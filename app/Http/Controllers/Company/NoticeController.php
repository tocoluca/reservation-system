<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Services\NoticeImageProcessor;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeImageProcessor $imageProcessor) {}

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
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:10000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [], [
            'title' => 'タイトル',
            'content' => '本文',
            'image' => '画像',
            'start_date' => '掲載開始日',
            'end_date' => '掲載終了日',
        ]);

        $data['company_id'] = $company->id;
        $data['is_important'] = $request->boolean('is_important');
        $data['is_active'] = $request->boolean('is_active');
        $data['image'] = $request->hasFile('image')
            ? $this->imageProcessor->store($request->file('image'), $company->id)
            : null;

        Notice::create($data);

        return redirect()->route('company.notices.index')
            ->with('success', '登録しました');
    }

    public function edit(Notice $notice)
    {
        $this->ensureCompanyNotice($notice);

        return view('company.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {

        $company = auth()->guard('company')->user()->company;
        $this->ensureCompanyNotice($notice);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:10000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'remove_image' => 'nullable|boolean',
        ], [], [
            'title' => 'タイトル',
            'content' => '本文',
            'image' => '画像',
            'start_date' => '掲載開始日',
            'end_date' => '掲載終了日',
        ]);

        $oldImagePath = $notice->image;

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageProcessor->store($request->file('image'), $company->id);
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        $data['is_important'] = $request->boolean('is_important');
        $data['is_active'] = $request->boolean('is_active');
        unset($data['remove_image']);

        $notice->update($data);

        if (($request->hasFile('image') || $request->boolean('remove_image')) && $oldImagePath) {
            $this->imageProcessor->delete($oldImagePath, $company->id);
        }

        return redirect()->route('company.notices.index')
            ->with('success', '更新しました');
    }

    public function destroy(Notice $notice)
    {
        $company = auth()->guard('company')->user()->company;
        $this->ensureCompanyNotice($notice);
        $oldImagePath = $notice->image;

        $notice->delete();
        $this->imageProcessor->delete($oldImagePath, $company->id);

        return redirect()->back()->with('success', '削除しました');
    }

    private function ensureCompanyNotice(Notice $notice): void
    {
        $companyId = auth()->guard('company')->user()->company_id;

        abort_unless((int) $notice->company_id === (int) $companyId, 404);
    }
}
