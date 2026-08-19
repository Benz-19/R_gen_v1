<?php
namespace App\Http\Controllers;

class PagesController{
    public function help_center(){
        return view('/pages/help_center');
    }

    public function system_status(){
        return view('/pages/system_status');
    }

    public function privacy(){
        return view('/pages/privacy');
    }
}