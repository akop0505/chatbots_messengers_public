<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarNav extends Model
{
    protected $fillable = [
        "label",
        "path",
        "name"
    ];


    /**
     * The roles that belong to the SidebarNav
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class,'sidebar_nav_has_roles','sidebar_nav_id','role_id');
    }

}