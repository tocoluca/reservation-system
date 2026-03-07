<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuTag;

class MenuController extends Controller
{

public function index(Request $request)
{

    $company = auth()->guard('company')->user()->company;

    $query = Menu::where('company_id',$company->id)
        ->with(['category','tags']);

    // カテゴリフィルター
    if ($request->category_id) {

        $query->where('menu_category_id',$request->category_id);

    }

    // タグフィルター
    if ($request->tag_id) {

        $query->whereHas('tags', function ($q) use ($request) {

            $q->where('menu_tag_id',$request->tag_id);

        });

    }

    // 並び替え
    if ($request->sort == 'price') {

        $query->orderBy('price');

    } elseif ($request->sort == 'name') {

        $query->orderBy('name');

    }

    $menus = $query->get();

    $categories = MenuCategory::where('company_id',$company->id)->get();

    $tags = MenuTag::where('company_id',$company->id)->get();

    return view('company.menu.index',compact(
        'menus',
        'categories',
        'tags'
    ));

}


public function create()
{

$company = auth()->guard('company')->user()->company;

$categories = MenuCategory::where('company_id',$company->id)->get();
$tags = MenuTag::where('company_id',$company->id)->get();

return view('company.menu.create',compact('categories','tags'));

}

public function store(Request $request)
{

$company = auth()->guard('company')->user()->company;

$request->validate([

'menu_category_id'=>'required',
'name'=>'required|max:100',
'description'=>'nullable|max:500',
'duration'=>'required|integer',
'price'=>'required|integer',
'tags'=>'nullable|array'

]);

$menu = Menu::create([

'company_id'=>$company->id,
'menu_category_id'=>$request->menu_category_id,
'name'=>$request->name,
'description'=>$request->description,
'duration'=>$request->duration,
'price'=>$request->price

]);

if ($request->tags) {

$menu->tags()->sync($request->tags);

}

return redirect()->route('company.menu.index');

}

public function edit(Menu $menu)
{

$company = auth()->guard('company')->user()->company;

$categories = MenuCategory::where('company_id',$company->id)->get();

$tags = MenuTag::where('company_id',$company->id)->get();

return view('company.menu.edit',compact(
'menu',
'categories',
'tags'
));

}

public function update(Request $request, Menu $menu)
{

$request->validate([

'menu_category_id'=>'required',
'name'=>'required|max:100',
'description'=>'nullable|max:500',
'duration'=>'required|integer',
'price'=>'required|integer'

]);

$menu->update([

'menu_category_id'=>$request->menu_category_id,
'name'=>$request->name,
'description'=>$request->description,
'duration'=>$request->duration,
'price'=>$request->price

]);

$menu->tags()->sync($request->tags ?? []);

return redirect()->route('company.menu.index');

}

public function destroy(Menu $menu)
{

$menu->tags()->detach();

$menu->delete();

return back()->with('success','メニューを削除しました');

}


}