<?php
namespace App\Http\Controllers\Auth;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Request;

class LoginAuthController extends AuthController{

    public function loginPage(){
        return view('/auth/login');
    }

    public function login(Request $request){

        $request->validate([
            'email' => 'required | unique:users',
            'password' => 'required'
        ]);

        $auth_service = new AuthService();
        $res = $auth_service->login($request);

        return $res['status'] === true ? view('/dashboard') : back()->with('error', $res['message']);
    }
}