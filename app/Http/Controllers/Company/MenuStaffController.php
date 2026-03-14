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
        $company = auth()->guard('company')->user()->company;

        $menus = Menu::where('company_id',$company->id)->get();
        $staffs = Staff::where('company_id',$company->id)->get();

        $relations = DB::table('menu_staff')->get();

        return view('company.menu_staff',[
            'menus'=>$menus,
            'staffs'=>$staffs,
            'relations'=>$relations
        ]);
    }


    public function update(Request $request)
    {

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