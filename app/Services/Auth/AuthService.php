<?php
namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService{

    public function register($credentials){
        
    }

    public function login($credentials){
        $user = new User();
        // Check if the user exists
        $is_user = $user->where('email', $credentials['email'])->first();

        if(!$is_user){
            return [
                'status' => false,
                'message' => 'user does not exists, create an account.'
            ];
        }

        //verify password
        if(!Hash::check($credentials['password'], $is_user['password'])){
            return [
                'status' => false,
                 'message' => 'Invalid login credentials.'   
            ];
        }

        return [
            'status' => true,
            'user_id' => $is_user->id
        ]; //returns a status and any other information for the Session
    }
}