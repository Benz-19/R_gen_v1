<?php
namespace App\Http\Controllers\Auth;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;

class LoginAuthController extends AuthController{

    public function loginPage(){
        return view('/auth/login');
    }

    public function login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $auth_service = new LoginService();
        $res = $auth_service->login($request);

        if($res && isset($res['user_id'], $res['user_type']) && $res['user_type'] != ''){
            $request->session()->put('user_id', $res['user_id']);
            $request->session()->put('user_type', $res['user_type']);
            $request->session()->save();
            
            return redirect()->intended('/dashboard');
        }

        return back()->with('fail', $res['message']);
    }
}