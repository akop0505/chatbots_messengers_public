<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PassagesAction extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAIL = 'fail';

    protected $fillable = [
        'passage_id',
        'project_id',
        'lead_id',
        'type',
        'options',
        'start_at',
        'end_at',
        'status',
        'error',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'start_at', 'end_at'];

    protected $casts = [
        'options' => 'array'
    ];

    public function project(){
        return $this->belongsTo(Project::class,'project_id','id');
    }

    public function lead(){
        return $this->belongsTo(Lead::class,'lead_id','id');
    }

    public function passage(){
        return $this->belongsTo(Passages::class,'passage_id','id');
    }
}
