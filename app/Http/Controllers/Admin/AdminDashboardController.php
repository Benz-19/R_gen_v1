<?php
namespace App\Http\Controllers\User\Admin;


class AdminDashboardController{

    public function dashboardPage(){
        return view('/admin/dashboard');
    }
}