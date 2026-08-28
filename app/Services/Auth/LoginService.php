<?php
namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserDetail;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Hash;

class LoginService extends AuthService{

    public function login($credentials){
        
        // Check if the user exists
        if(!$this->isUser($credentials['email'])){
            return [
                'status' => false,
                'message' => 'user does not exists, create an account.'
                ];
        }
                
        $user = new User();
        $this->isUser($credentials['email']);
        $is_user = $user->where('email', $credentials['email'])->first();
                
        
        //verify password
        if(!Hash::check($credentials['password'], $is_user['password'])){
            return [
                'status' => false,
                'user_id' => '',
                'user_type' => '',
                'message' => 'Invalid login credentials.'   
                ];
        }
                
        // get the user type (admin/employee/individual user)
        $isAdmin = UserDetail::where('user_id', $is_user->id)->value('is_admin');
        $accountType = UserDetail::where('user_id', $is_user->id)->value('account_type');
        
        $user_type = '';

        if($isAdmin === 1){
            $user_type = 'admin';
        }elseif($is_user !==1 && $accountType === 'organization'){
            $user_type = 'employee';
        }else{
            $user_type = 'individual';
        }

        return [
            'status' => true,
            'user_id' => $is_user->id,
            'user_type' => $user_type
        ]; //returns a status and any other information for the Session
    }
}