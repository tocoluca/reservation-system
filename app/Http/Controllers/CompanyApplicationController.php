<?php

namespace App\Http\Controllers;

use App\Models\CompanyApplication;
use Illuminate\Http\Request;

class CompanyApplicationController extends Controller
{
	public function create()
	{
	return view('apply');
	}

	public function index()
	{
	    $applications = CompanyApplication::latest()->get();
	    return view('admin.applications', compact('applications'));
	}

	public function store(Request $request)
	{
	$request->validate([
	    'company_name' => 'required',
	    'industry_type' => 'required',
	    'email' => 'required|email'
	]);

	CompanyApplication::create($request->all());

	return back()->with('success', '申込みを受け付けました');
	}

	public function reject($id)
	{
	    $app = CompanyApplication::findOrFail($id);
	    $app->update(['status' => 'rejected']);

	    return back()->with('success', '拒否しました');
	}

}