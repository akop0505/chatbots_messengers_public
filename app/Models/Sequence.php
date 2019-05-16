<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sequence extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'name',
        'api_alias',
        'project_id',
        'by_default',
        'for_nonworking_time',
        'options',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $casts = [
        'options' => 'array'
    ];

    public function project(){
        return $this->belongsTo(Project::class,'project_id','id');
    }
}
