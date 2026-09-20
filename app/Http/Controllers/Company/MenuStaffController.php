<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuStaffController extends Controller
{
    public function index()
    {
        $current = auth()->guard('company')->user();
        abort_if(! $current || ! $current->canDashboard('card.menu_staff'), 403);

        $company = $current->company;

        $menus = Menu::with('category')
            ->where('company_id', $company->id)
            ->orderBy('menu_category_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->orderBy('priority_order')
            ->orderBy('id')
            ->get();

        $relations = DB::table('menu_staff')
            ->whereIn('menu_id', $menus->pluck('id'))
            ->get();

        return view('company.menu_staff', [
            'menus' => $menus,
            'staffs' => $staffs,
            'relations' => $relations,
        ]);
    }

    public function update(Request $request)
    {
        $current = auth()->guard('company')->user();
        abort_if(! $current || ! $current->canDashboard('card.menu_staff'), 403);

        $validated = $request->validate([
            'relations' => ['nullable', 'array'],
            'relations.*' => ['array'],
            'relations.*.*' => ['integer'],
        ]);

        $company = $current->company;
        $menuIds = Menu::where('company_id', $company->id)->pluck('id')->map(fn ($id) => (int) $id);
        $staffIds = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $allowedMenus = $menuIds->flip();
        $allowedStaff = $staffIds->flip();
        $rows = [];

        foreach ((array) ($validated['relations'] ?? []) as $menuId => $selectedStaffIds) {
            $menuId = (int) $menuId;

            if (! $allowedMenus->has($menuId)) {
                continue;
            }

            foreach (array_unique(array_map('intval', (array) $selectedStaffIds)) as $staffId) {
                if (! $allowedStaff->has($staffId)) {
                    continue;
                }

                $rows[] = [
                    'menu_id' => $menuId,
                    'staff_id' => $staffId,
                ];
            }
        }

        DB::transaction(function () use ($menuIds, $rows) {
            if ($menuIds->isNotEmpty()) {
                DB::table('menu_staff')->whereIn('menu_id', $menuIds)->delete();
            }

            if ($rows !== []) {
                DB::table('menu_staff')->insert($rows);
            }
        });

        return back()->with('success', '保存しました');
    }
}
