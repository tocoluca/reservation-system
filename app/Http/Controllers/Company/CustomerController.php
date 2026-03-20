<?php

namespace App\Http\Controllers\Company;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomerPhoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

public function index(Request $request)
{

$company = auth()->guard('company')->user()->company;

$query = Customer::where('company_id',$company->id);

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

$path = $request->file('photo')->store('customers','public');

CustomerPhoto::create([

'customer_id'=>$customer->id,
'path'=>$path

]);

return back()->with('success','写真を追加しました');

}

}