<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    private function authorizeRole()
    {
        $role = Auth::guard('company')->user()->role;

        if (!in_array($role, ['master','leader','area_leader'])) {
            abort(403);
        }
    }

    public function index()
    {
        $company = Auth::guard('company')->user()->company;

	//コードの昇順で表示
	$staffs = $company->staff()
	    ->orderByRaw("
	        CASE role
	            WHEN 'master' THEN 1
	            WHEN 'area_leader' THEN 2
	            WHEN 'leader' THEN 3
	            ELSE 4
	        END
	    ")
	    ->orderBy('staff_code')
	    ->get();

         return view('company.staff.index', compact('staffs'));
    }

    public function create()
    {
        $this->authorizeRole();

        return view('company.staff.create');
    }

	public function store(Request $request)

	{
	    $this->authorizeRole();

	    $current = Auth::guard('company')->user();
	    $company = $current->company;

	    $request->validate([
	        'name' => 'required',
	        'password' => 'required|min:8',
	        'role' => 'required'
	    ]);

	    // 上位権限は作れない
	    if ($this->roleLevel($request->role) > $this->roleLevel($current->role)) {
		return redirect()->route('company.staff.create')->with('error','上位権限は作成できません');
	    }

	    DB::transaction(function () use ($request, $company) {

	        // 🔒 数値として最大値取得（安全）
	        $lastCode = DB::table('staff')
	            ->where('company_id', $company->id)
	            ->lockForUpdate()
	            ->orderByRaw('CAST(staff_code AS UNSIGNED) DESC')
	            ->value('staff_code');

	        $nextNumber = $lastCode ? intval($lastCode) + 1 : 1;
	        $newCode = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

	        // 画像保存
	        $path = null;
	        if ($request->hasFile('image')) {
	            $path = $request->file('image')
	                ->store('staff', 'public');
	        }

	        // Staff作成
	        $staff = Staff::create([
	            'company_id' => $company->id,
	            'staff_code' => $newCode,
	            'name' => $request->name,
	            'password' => Hash::make($request->password),
	            'role' => $request->role,
	            'is_reservable' => $request->boolean('is_reservable'),
	            'priority_order' => $request->priority_order ?? 0,
	            'image_path' => $path,
	            'force_password_change' => true
	        ]);

	        // area_leader の場合のみ店舗紐付け
	        if ($request->role === 'area_leader' && $request->store_ids) {
	            $staff->stores()->sync($request->store_ids);
	        }
	    });

	    return redirect()
	        ->route('company.staff.index')
	        ->with('success','担当者を登録しました');
	}
    public function edit(Staff $staff)
    {
        $this->authorizeRole();

	$current = Auth::guard('company')->user();

//	Log::debug('$staff->role'.$staff->role);
//	Log::debug('$current->role'.$current->role);

	if ($this->roleLevel($staff->role) >
	    $this->roleLevel($current->role)) {

	return redirect()->route('company.staff.index')->with('error','上位権限は編集できません');

	}


	if ($staff->image_path) {
	    Storage::disk('public')->delete($staff->image_path);
	}

        return view('company.staff.edit', compact('staff'));
    }

public function update(Request $request, Staff $staff)
{
    $this->authorizeRole();

    $current = Auth::guard('company')->user();

    // 🔒 上位権限は編集不可
    if ($this->roleLevel($staff->role) > $this->roleLevel($current->role)) {
	return redirect()->route('company.staff.index')->with('error','上位権限は編集できません');
    }

    $request->validate([
        'name' => 'required',
        'image' => 'nullable|image|max:2048'
    ]);

    DB::transaction(function () use ($request, $staff) {

        $oldImagePath = $staff->image_path;
        $newImagePath = $oldImagePath;

        // ✅ ① 新画像アップロード
        if ($request->hasFile('image')) {

            $newImagePath = $request->file('image')
                ->store('staff', 'public');
        }

        // ✅ ② DB更新
        $staff->update([
            'name' => $request->name,
            'role' => $request->role,
            'is_reservable' => $request->boolean('is_reservable'),
            'priority_order' => $request->priority_order ?? 0,
            'image_path' => $newImagePath
        ]);

        // ✅ ③ DB成功後に旧画像削除
        if ($request->hasFile('image') && $oldImagePath) {
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }
    });

    return back()->with('success', '更新しました');
}

    public function destroy(Staff $staff)
    {
        $this->authorizeRole();

	$current = Auth::guard('company')->user();

	if ($this->roleLevel($staff->role) >
	    $this->roleLevel($current->role)) {

		return redirect()->route('company.staff.index')->with('error','上位権限は編集できません');
	}

        $staff->delete(); // 論理削除

        return back()->with('success','削除しました');
    }

	private function roleLevel($role)
	{
	    return match($role) {
	        'master' => 4,
	        'area_leader' => 3,
	        'leader' => 2,
	        default => 1
	    };
	}

	public function resetPassword($id)
	{
	    $current = Auth::guard('company')->user();

	    // 🔒 リーダー以上のみ許可
	    if (!in_array($current->role, ['leader','area_leader','master'])) {
	        abort(403);
	    }

	    $staff = Staff::where('company_id', $current->company_id)
	                  ->where('id', $id)
	                  ->firstOrFail();

	    // 🔒 上位権限は初期化不可
	    if ($this->roleLevel($staff->role) > $this->roleLevel($current->role)) {
		return redirect()->route('company.staff.index')->with('error','上位権限は初期化できません');
	    }

		//初期パスワード
	    $staff->password = Hash::make('12345678');
	    $staff->force_password_change = true;
	    $staff->save();

	    return back()->with('success','パスワードを初期化しました');
	}

}