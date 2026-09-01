<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceUser extends Model{
    protected $fillable = [
        'user_id',
        'workspace_id',
        'is_verified',
        ];

    protected $table = 'workspace_user';
}