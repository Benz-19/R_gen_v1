<?php
namespace App\Http\Controllers\Auth;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Request;

class RegisterAuthController extends AuthController{
    public function registerPage(){
        return view('/auth/register');
    }

    
}