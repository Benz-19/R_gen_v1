<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller{

    public function index(Request $request){
        $userType = $request->session()->get('user_type');

        return match($userType){
            'admin' => view('admin.dashboard'),
            'employee' => view('employee.dashboard'),
            default => view('individual_user.dashboard')
        };
    }
}