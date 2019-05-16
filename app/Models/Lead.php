<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'last_name',
        'first_name',
        'father_name',
        'phone',
        'email',
        'project_id',
        'owner_id',
        'status',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function owner(){
        return $this->belongsTo(User::class,'owner_id','id');
    }

    public function project(){
        return $this->belongsTo(Project::class,'project_id','id');
    }

    public function status(){
        return $this->belongsTo(LeadsStatusDict::class,'status','id');
    }

    public function passage(){
        return $this->hasOne(Passages::class,'lead_id','id');
    }
}
