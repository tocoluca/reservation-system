<?php

namespace App\Http\Controllers\Company;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomerPhoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//画像圧縮
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class CustomerController extends Controller
{

public function index(Request $request)
{

$company = auth()->guard('company')->user()->company;

$query = Customer::where('company_id', $company->id)
    ->with(['photos' => function ($q) {
        $q->latest('id');
    }]);


if($request->filled('keyword')){

$query->where(function($q) use ($request){

$q->where('name','like','%'.$request->keyword.'%')
->orWhere('phone','like','%'.$request->keyword.'%');

});

}

$customers = $query
->orderByDesc('last_visit')
->paginate(30)
->appends($request->query());

return view('company.customers.index',compact('customers'));

}

public function show($id)
{

$company = auth()->guard('company')->user()->company;

$customer = Customer::where('company_id',$company->id)
->with([
'reservations.staff',
'reservations.menus',
'notes',
'photos'
])
->findOrFail($id);

return view('company.customers.show',compact('customer'));

}


public function note(Request $request,$id)
{

$request->validate([
'note'=>'required|string|max:2000'
]);

$company = auth()->guard('company')->user()->company;

$customer = Customer::where('company_id',$company->id)
->findOrFail($id);

CustomerNote::create([

'customer_id'=>$customer->id,
'staff_id'=>null,
'note'=>$request->note

]);

return back()->with('success','メモを保存しました');

}


public function photo(Request $request,$id)
{

$request->validate([
'photo'=>'required|image|max:5000'
]);

$company = auth()->guard('company')->user()->company;

$customer = Customer::where('company_id',$company->id)
->findOrFail($id);

//$path = $request->file('photo')->store('customers','public');

$path = null;
// 画像保存

	if ($request->hasFile('photo')) {

	    $file = $request->file('photo');

		$manager = new ImageManager(new Driver());
		$image = $manager->read($file);

		// 最大800pxに縮小
		$image->scaleDown(width: 800);

		// WebP変換
		$encoded = $image->toWebp(quality: 85);


	    $dir = public_path('companies/'.$company->id.'/customer');

	    if (!file_exists($dir)) {
	        mkdir($dir, 0755, true);
	    }

	    $filename = uniqid().'.webp';

	    $file->move($dir, $filename);

	    $path = 'companies/'.$company->id.'/customer/'.$filename;

	    //画像の保存
	    file_put_contents($path, $encoded);

	}

CustomerPhoto::create([

'customer_id'=>$customer->id,
'path'=>$path

]);


return back()->with('success','写真を追加しました');

}

// メモ削除
public function deleteNote($id)
{
    $company = auth()->guard('company')->user()->company;

    $note = CustomerNote::whereHas('customer', function($q) use ($company){
        $q->where('company_id', $company->id);
    })->findOrFail($id);

    $note->delete();

    return back()->with('success','メモを削除しました');
}


// 写真削除
public function deletePhoto($id)
{
    $company = auth()->guard('company')->user()->company;

    $photo = CustomerPhoto::whereHas('customer', function($q) use ($company){
        $q->where('company_id', $company->id);
    })->findOrFail($id);

    // ファイル削除
    if ($photo->path && file_exists(public_path($photo->path))) {
        unlink(public_path($photo->path));
    }

    $photo->delete();

    return back()->with('success','写真を削除しました');
}


}