<?php
namespace App\Http\Controllers\User\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller{

    public function index(Request $request){
        $metrics = [
            'total_users' => 0,
            'active_workspace' => 'default organization',
            'pending_exceptions' => 0,
            'total_reconciled' => 0,
        ];

        return view('/dashboard', compact('metrics'));
    }
}