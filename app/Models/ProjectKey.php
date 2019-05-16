<?php


namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectKey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'access_key',
        'inactive',
        'user_id',
        'created_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    //protected $appends = ['created_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}