<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected  $fillable = ['name','description'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    protected $appends = [
        'removable'
    ];

    private $relationTablesClass = [
        User::class
    ];

    public function getRemovableAttribute()
    {
        foreach ($this->relationTablesClass as $relClass) {
            $count = $relClass::where(['role_id'=>$this->id])->count();
            if($count){
                return 0;
            }
        }
        return 1;
    }
}
