<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Individual\IndividualDashboardController;
use Illuminate\Http\Request;

class DashboardController extends Controller{

    public function index(Request $request){
        $userType = $request->session()->get('user_type');

        return match($userType){
            'admin' => new AdminDashboardController()->index($request),
            'employee' => new EmployeeDashboardController()->index($request),
            default => new IndividualDashboardController()->index($request)
        };
    }
}