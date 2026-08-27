<?php
namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService{

    protected function isUser(string $email){
        return User::where('email', $email)->exists();
    }
}