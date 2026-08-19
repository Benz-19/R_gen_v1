<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

class AuthController{
    public function loginPage(){
        return view('/auth/login');
    }
}