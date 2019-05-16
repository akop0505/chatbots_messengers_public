<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMessenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'gateway_id',
        'permission_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($projectM) {
            foreach ($projectM->gatewaySettings as $gs) {
                $gs->delete();
            }
        });
    }

    public function project(){
        return $this->belongsTo(Project::class,'project_id','id');
    }

    public function messenger(){
        return $this->belongsTo(Messenger::class,'messenger_id','id');
    }

    public function permission(){
        return $this->belongsTo(Permission::class,'permission_id','id');
    }

    public function gateway(){
        return $this->belongsTo(Gateway::class,'gateway_id','id');
    }

    public function subscribe(){
        return $this->hasOne(GatewaySubscribe::class,'project_messenger_id','id');
    }

    public function gatewaySettings(){
        return $this->hasMany(GatewaySetting::class,'project_messenger_id','id');
    }

    public function getGatewaySettings($field = null){
        $modelsCollection = $this->gatewaySettings()->get();
        $modelsCollection = $modelsCollection->pluck('field_value','field_name');
        if($field !== null){
           return $modelsCollection[$field];
        }
        return $modelsCollection;
    }
}