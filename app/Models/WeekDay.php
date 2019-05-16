<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class WeekDay extends  Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'from',
        'to',
        'enabled',
        'order',
        'title'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}