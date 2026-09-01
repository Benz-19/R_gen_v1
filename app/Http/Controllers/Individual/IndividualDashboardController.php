<?php
namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndividualDashboardController extends Controller{

    public function index(Request $request){

        return view('individual_user.dashboard');
    }
}