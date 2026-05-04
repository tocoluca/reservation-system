<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class MenuStaffController extends Controller
{

    public function index()
    {
        $current = auth()->guard('company')->user();
        abort_if(!$current || !$current->canDashboard('card.menu_staff'), 403);

        $company = $current->company;

        $menus = Menu::where('company_id',$company->id)->get();
        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->get();

        $relations = DB::table('menu_staff')->get();

        return view('company.menu_staff',[
            'menus'=>$menus,
            'staffs'=>$staffs,
            'relations'=>$relations
        ]);
    }


    public function update(Request $request)
    {
        $current = auth()->guard('company')->user();
        abort_if(!$current || !$current->canDashboard('card.menu_staff'), 403);

        DB::table('menu_staff')->truncate();

        foreach($request->relations ?? [] as $menuId=>$staffIds){

            foreach($staffIds as $staffId){

                DB::table('menu_staff')->insert([
                    'menu_id'=>$menuId,
                    'staff_id'=>$staffId
                ]);

            }

        }

        return back()->with('success','保存しました');
    }

}
