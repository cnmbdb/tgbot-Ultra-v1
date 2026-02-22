<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Auth;

class Admin extends Authenticatable
{
    use Notifiable;
    
    use HasRoles;

    protected $guard_name = 'web';

    protected $table = 't_admin';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = [
        'password'
    ];

}
