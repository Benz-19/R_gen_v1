<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

class DashboardController extends Controller{

    public function index(Request $request){
        $userType = $request->session()->get('user_type');

        return match($userType){
            1 => view('admin.dashboard'),
            2 => view('employee.dashboard'),
            default => view('individual_user.dashboard')
        };
    }
}