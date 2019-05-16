<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class ProjectOfficePhone extends  Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'phone',
        'extra',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}