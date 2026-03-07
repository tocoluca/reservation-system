<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuCategory;
use App\Models\MenuTag;

class MenuSettingController extends Controller
{

public function index()
{

$company = auth()->guard('company')->user()->company;

$categories = MenuCategory::where('company_id',$company->id)->get();

$tags = MenuTag::where('company_id',$company->id)->get();

return view('company.menu.settings',compact(
'categories',
'tags'
));

}

public function storeCategory(Request $request)
{

$company = auth()->guard('company')->user()->company;

$request->validate([
'name'=>'required|max:50'
]);

MenuCategory::create([
'company_id'=>$company->id,
'name'=>$request->name
]);

return back();

}

public function storeTag(Request $request)
{

$company = auth()->guard('company')->user()->company;

$request->validate([
'name'=>'required|max:50'
]);

MenuTag::create([
'company_id'=>$company->id,
'name'=>$request->name
]);

return back();

}

public function deleteCategory($id)
{

    $company = auth()->guard('company')->user()->company;

    $category = MenuCategory::where('company_id',$company->id)
        ->with('menus')
        ->where('id',$id)
        ->firstOrFail();

    if ($category->menus->count() > 0) {

        $menuNames = $category->menus->pluck('name')->implode('、');

        return back()->with(
            'error',
            "このカテゴリーは次のメニューで使用されています：".$menuNames
        );

    }

    $category->delete();

    return back()->with('success','カテゴリーを削除しました');


}
public function deleteTag($id)
{

$company = auth()->guard('company')->user()->company;

$tag = MenuTag::where('company_id',$company->id)
->findOrFail($id);

$tag->menus()->detach();

$tag->delete();

return back();

}

}