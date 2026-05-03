<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class StaffController extends Controller
{
    private function currentStaff(): Staff
    {
        return Auth::guard('company')->user();
    }

    private function authorizeStaffIndex(): void
    {
        $current = $this->currentStaff();

        if (!$current->canDashboard('card.staff')) {
            abort(403, '讓ｩ髯舌′縺ゅｊ縺ｾ縺帙ｓ');
        }
    }

    private function authorizeStaffManage(): void
    {
        $current = $this->currentStaff();

        // 縺ｾ縺壹・螳牙・蛛ｴ縺ｧ master 縺ｮ縺ｿ螟画峩邉ｻ繧定ｨｱ蜿ｯ
        if (!$current->isMaster()) {
            abort(403, '諡・ｽ楢・ｮ｡逅・・讓ｩ髯舌′縺ゅｊ縺ｾ縺帙ｓ');
        }
    }

    private function ensureSameCompany(Staff $staff): void
    {
        $current = $this->currentStaff();

        if ((int) $staff->company_id !== (int) $current->company_id) {
            abort(404);
        }
    }

    private function roleLevel(?string $role): int
    {
        return match ($role) {
            'master' => 5,
            'store_operator' => 4,
            'area_leader' => 3,
            'leader' => 2,
            default => 1,
        };
    }

    private function canResetStaffPassword(Staff $current, Staff $target): bool
    {
        if ((int) $current->id === (int) $target->id) {
            return false;
        }

        if ($current->isMaster()) {
            return true;
        }

        if (in_array($current->role, ['leader', 'area_leader'], true)) {
            return $target->role !== 'master';
        }

        return false;
    }

    private function generateStaffCode(int $companyId, string $role): string
    {
        if ($role === 'store_operator') {
            $prefix = 'SHOP';

            $existingCodes = DB::table('staff')
                ->where('company_id', $companyId)
                ->where('role', 'store_operator')
                ->where('staff_code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->pluck('staff_code');

            $maxNumber = $existingCodes
                ->map(fn ($code) => (int) str_replace($prefix, '', $code))
                ->max() ?? 0;

            $nextNumber = $maxNumber + 1;

            if ($nextNumber > 99) {
                throw new \RuntimeException('店舗運営ユーザーコードは SHOP99 までです。');
            }

            return $prefix . str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
        }

        if ($role === 'master') {
            $prefix = 'MASTER';

            $existingCodes = DB::table('staff')
                ->where('company_id', $companyId)
                ->where('role', 'master')
                ->where('staff_code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->pluck('staff_code');

            $maxNumber = $existingCodes
                ->map(fn ($code) => (int) str_replace($prefix, '', $code))
                ->max() ?? 0;

            $nextNumber = $maxNumber + 1;

            if ($nextNumber > 99) {
                throw new \RuntimeException('マスターユーザーコードは MASTER99 までです。');
            }

            return $prefix . str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
        }

        $lastCode = DB::table('staff')
            ->where('company_id', $companyId)
            ->whereRaw('staff_code REGEXP "^[0-9]+$"')
            ->lockForUpdate()
            ->orderByRaw('CAST(staff_code AS UNSIGNED) DESC')
            ->value('staff_code');

        $nextNumber = $lastCode ? ((int) $lastCode + 1) : 1;

        return str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

	public function index()
	{
	    $this->authorizeStaffIndex();

	    $company = $this->currentStaff()->company;

	    $staffs = $company->staff()
	        ->orderByRaw("
	            CASE
	                WHEN role = 'master' THEN 1
	                WHEN role = 'store_operator' THEN 2
	                WHEN role = 'area_leader' THEN 3
	                WHEN role = 'leader' THEN 4
	                ELSE 5
	            END ASC
	        ")
	        ->orderByRaw("
	            CASE
	                WHEN staff_code LIKE 'MASTER%' THEN CAST(REPLACE(staff_code, 'MASTER', '') AS UNSIGNED)
	                WHEN staff_code LIKE 'SHOP%' THEN CAST(REPLACE(staff_code, 'SHOP', '') AS UNSIGNED)
	                WHEN staff_code REGEXP '^[0-9]+$' THEN CAST(staff_code AS UNSIGNED)
	                ELSE 999999
	            END ASC
	        ")
	        ->get();

	    return view('company.staff.index', compact('staffs'));
	}

    public function create()
    {
        $this->authorizeStaffManage();

        return view('company.staff.create');
    }

    public function store(Request $request)
    {
        $this->authorizeStaffManage();

        $current = $this->currentStaff();
        $company = $current->company;

		$request->validate([
		    'name' => 'required|string|max:255',
		    'password' => 'required|string|min:8',
		    'role' => 'required|string|in:staff,leader,area_leader,store_operator,master',
		    'image' => 'nullable|image|max:2048',
		    'priority_order' => 'nullable|integer|min:0',
		    'nomination_fee' => 'nullable|numeric|min:0',
		    'retired_at' => 'nullable|date',
		]);

        if ($this->roleLevel($request->role) > $this->roleLevel($current->role)) {
            return redirect()
                ->route('company.staff.create')
                ->with('error', '荳贋ｽ肴ｨｩ髯舌・菴懈・縺ｧ縺阪∪縺帙ｓ')
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $company) {
                $newCode = $this->generateStaffCode($company->id, $request->role);

                $path = null;

                if ($request->hasFile('image')) {
                    $file = $request->file('image');

                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file);
                    $image->scaleDown(width: 800);
                    $encoded = $image->toWebp(quality: 85);

                    $dir = public_path('companies/' . $company->id . '/staff');
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    $filename = uniqid() . '.webp';
                    $path = 'companies/' . $company->id . '/staff/' . $filename;

                    file_put_contents(public_path($path), $encoded);
                }

				$staff = Staff::create([
				    'company_id' => $company->id,
				    'staff_code' => $newCode,
				    'name' => $request->name,
				    'password' => Hash::make($request->password),
				    'role' => $request->role,
				    'is_reservable' => $request->boolean('is_reservable'),
				    'priority_order' => $request->priority_order ?? 0,
				    'nomination_fee' => $request->nomination_fee ?? 0,
				    'image_path' => $path,
				    'retired_at' => $request->filled('retired_at') ? $request->retired_at : null,
				    'force_password_change' => true,
				]);

                if ($request->role === 'area_leader' && $request->filled('store_ids')) {
                    $staff->stores()->sync($request->store_ids);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('company.staff.create')
                ->with('error', $e->getMessage())
                ->withInput();
        }

        return redirect()
            ->route('company.staff.index')
            ->with('success', '諡・ｽ楢・ｒ逋ｻ骭ｲ縺励∪縺励◆');
    }

    public function edit(Staff $staff)
    {
        $this->authorizeStaffManage();
        $this->ensureSameCompany($staff);

        $current = $this->currentStaff();

        if ($this->roleLevel($staff->role) > $this->roleLevel($current->role)) {
            return redirect()
                ->route('company.staff.index')
                ->with('error', '荳贋ｽ肴ｨｩ髯舌・邱ｨ髮・〒縺阪∪縺帙ｓ');
        }

        return view('company.staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $this->authorizeStaffManage();
        $this->ensureSameCompany($staff);

        $current = $this->currentStaff();

        if ($this->roleLevel($staff->role) > $this->roleLevel($current->role)) {
            return redirect()
                ->route('company.staff.index')
                ->with('error', '荳贋ｽ肴ｨｩ髯舌・邱ｨ髮・〒縺阪∪縺帙ｓ');
        }

        if ($this->roleLevel($request->input('role')) > $this->roleLevel($current->role)) {
            return redirect()
                ->route('company.staff.edit', $staff->id)
                ->with('error', '荳贋ｽ肴ｨｩ髯舌↓縺ｯ螟画峩縺ｧ縺阪∪縺帙ｓ')
                ->withInput();
        }

		$request->validate([
		    'name' => 'required|string|max:255',
		    'role' => 'required|string|in:staff,leader,area_leader,store_operator,master',
		    'image' => 'nullable|image|max:2048',
		    'priority_order' => 'nullable|integer|min:0',
		    'nomination_fee' => 'nullable|numeric|min:0',
		    'retired_at' => 'nullable|date',
		]);

        DB::transaction(function () use ($request, $staff) {
            $oldImagePath = $staff->image_path;
            $newImagePath = $oldImagePath;

            if ($request->hasFile('image')) {
                $file = $request->file('image');

                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                $image->scaleDown(width: 800);
                $encoded = $image->toWebp(quality: 85);

                $dir = public_path('companies/' . $staff->company_id . '/staff');
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $filename = uniqid() . '.webp';
                $newImagePath = 'companies/' . $staff->company_id . '/staff/' . $filename;

                file_put_contents(public_path($newImagePath), $encoded);
            }

			$staff->update([
			    'name' => $request->name,
			    'role' => $request->role,
			    'is_reservable' => $request->boolean('is_reservable'),
			    'priority_order' => $request->priority_order ?? 0,
			    'nomination_fee' => $request->nomination_fee ?? 0,
			    'image_path' => $newImagePath,
			    'retired_at' => $request->filled('retired_at') ? $request->retired_at : null,
			]);

            if (
                $request->hasFile('image') &&
                $oldImagePath &&
                file_exists(public_path($oldImagePath))
            ) {
                unlink(public_path($oldImagePath));
            }

            if ($request->role === 'area_leader' && $request->filled('store_ids')) {
                $staff->stores()->sync($request->store_ids);
            } elseif ($staff->role !== 'area_leader') {
                $staff->stores()->sync([]);
            }
        });

        return back()->with('success', '譖ｴ譁ｰ縺励∪縺励◆');
    }

    public function destroy(Staff $staff)
    {
        $this->authorizeStaffManage();
        $this->ensureSameCompany($staff);

        $current = $this->currentStaff();

        if ($this->roleLevel($staff->role) > $this->roleLevel($current->role)) {
            return redirect()
                ->route('company.staff.index')
                ->with('error', '荳贋ｽ肴ｨｩ髯舌・蜑企勁縺ｧ縺阪∪縺帙ｓ');
        }

        if ((int) $staff->id === (int) $current->id) {
            return redirect()
                ->route('company.staff.index')
                ->with('error', '閾ｪ蛻・・霄ｫ縺ｯ蜑企勁縺ｧ縺阪∪縺帙ｓ');
        }

        if ($staff->image_path && file_exists(public_path($staff->image_path))) {
            unlink(public_path($staff->image_path));
        }

        $staff->delete();

        return back()->with('success', '蜑企勁縺励∪縺励◆');
    }

    public function resetPassword(Request $request, $id)
    {
        $this->authorizeStaffIndex();

        $current = $this->currentStaff();

        $staff = Staff::where('company_id', $current->company_id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$this->canResetStaffPassword($current, $staff)) {
            return redirect()
                ->route('company.staff.index')
                ->with('error', 'この担当者のパスワードは初期化できません');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'initial_password' => ['required', 'string', 'min:8'],
        ], [
            'initial_password.required' => '初期パスワードを入力してください。',
            'initial_password.min' => '初期パスワードは8文字以上で入力してください。',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->with('error', $validator->errors()->first('initial_password'));
        }

        $validated = $validator->validated();

        $staff->password = Hash::make($validated['initial_password']);
        $staff->force_password_change = true;
        $staff->save();

        return back()->with('success', $staff->name . ' のパスワードを初期化しました');
    }
}

