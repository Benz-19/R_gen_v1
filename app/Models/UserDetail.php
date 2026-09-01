<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id',
        'account_type',
        'company_name',
        'is_company_verified',
        'is_admin',
        'primary_data_source',
        'workspace_code',
        'selected_role',
        'workspace_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}