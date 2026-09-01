<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Workspace extends Model
{
    protected $fillable = ['name', 'join_code', 'admin_user_id', 'user_id', 'is_verified'];

    public static function generateUniqueCode():string{
        return strtoupper(Str::random(8));
    }
}
