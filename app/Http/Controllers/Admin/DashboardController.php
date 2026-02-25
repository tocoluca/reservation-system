<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyApplication;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
	public function index()
	{
	    return view('admin.dashboard', [
	        'companyCount'  => Company::count(),
	        'pendingCount'  => CompanyApplication::where('status','pending')->count(),
	        'inactiveCount' => Company::where('is_active',false)->count()
	    ]);
	}
}