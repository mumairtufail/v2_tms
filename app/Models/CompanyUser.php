<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'password',
        'address',
        'is_admin',
        'company_id',
        'is_deleted',
        'phone'
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'company_user_id', 'role_id')
                    ->withTimestamps();
    }
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                    ->withPivot(['create', 'update', 'view', 'delete', 'logs', 'others'])
                    ->withTimestamps();
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }
}
