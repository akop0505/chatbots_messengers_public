<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Psy\Util\Json;

class Gateway extends Model
{
    protected $fillable = [
        'messenger_id',
        'name',
        'description',
        'link',
        'by_default',
        'removed',
        'config'
    ];

    private $relationTablesClass = [
        'App\Models\ProjectMessenger'
    ];

    protected $appends = [
        'removable'
    ];

    protected $dates = ['deleted_at'];

    public function getRemovableAttribute()
    {
        foreach ($this->relationTablesClass as $relClass){
            $count = $relClass::where(['gateway_id'=>$this->id])->count();
            if($count){
                return 0;
            }
        }
        return 1;
    }

    public function getConfigAttribute($value)
    {
        if($value === null){
            return [];
        }
        return json_decode($value);
    }

    public static function getAll($page,$limit){
        return self::query()
            ->where('removed','<>',1)
            ->with(['messenger'])
            ->offset($page*$limit)
            ->limit($limit)
            ->get();
    }

    public function messenger(){
        return $this->belongsTo(Messenger::class,'messenger_id','id');
    }

    public function isChannel($channel){
        $gatewayChannel = $this->messenger;
        if($gatewayChannel){
            return $gatewayChannel->alias === $channel;
        }
        return false;
    }
}