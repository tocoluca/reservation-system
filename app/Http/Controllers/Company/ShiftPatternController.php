<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShiftPattern;

class ShiftPatternController extends Controller
{

public function index()
{

$company = auth()->guard('company')->user()->company;

$patterns = ShiftPattern::where(
'company_id',
$company->id
)->orderBy('id')->get();

return view(
'company.shift_patterns',
compact('patterns')
);

}


public function store(Request $request)
{

$company = auth()->guard('company')->user()->company;

ShiftPattern::create([

'company_id'=>$company->id,
'name'=>$request->name,
'start_time'=>$request->start_time,
'end_time'=>$request->end_time,
'color'=>$request->color

]);

return back();

}


public function delete($id)
{

$company = auth()->guard('company')->user()->company;

ShiftPattern::where('id',$id)
->where('company_id',$company->id)
->delete();

return back();

}

}