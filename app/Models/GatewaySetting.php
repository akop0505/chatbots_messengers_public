<?php


namespace App\Models;

use App\Repositories\GatewaySettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GatewaySetting extends Model implements GatewaySettingsRepository
{
    use SoftDeletes;

    protected $fillable = [
        "project_messenger_id",
        "field_name",
        "field_value"
    ];

    protected $dates = ['deleted_at'];

    public function subscribe(){
        return $this->hasOne(GatewaySubscribe::class,'project_messenger_id','project_messenger_id');
    }

    public function getUsernameByToken($token){
        return  $this->query()
            ->select(['G2.field_value'])
            ->join('gateway_settings as G2','gateway_settings.project_messenger_id','=','G2.project_messenger_id')
            ->where([
                'gateway_settings.field_value'=>$token,
                'G2.field_name'=>'username'
            ])->first();
    }

    public function getFbSettingsByAppId($appId){
        return  $this->query()
            ->select(['G2.field_value'])
            ->join('gateway_settings as G2','gateway_settings.project_messenger_id','=','G2.project_messenger_id')
            ->where([
                'gateway_settings.field_value'=>$appId,
                'G2.field_name'=>'token'
            ])->first();
    }

    public function getTokenByUsernameAndId($username,$id){
         return $this->query()
            ->select(['G2.field_value'])
            ->join('gateway_settings as G2','gateway_settings.project_messenger_id','=','G2.project_messenger_id')
            ->where([
                'gateway_settings.field_value'=>$username,
                'G2.field_name'=>'token'
            ])
            ->where([
                ['G2.field_value','LIKE',"{$id}%"],
                'G2.field_name'=>'token'
            ])->first();
    }

    public function getProjectIdByChannelConfig($field,$value){
        return  $this->query()
            ->distinct()
            ->select(['gateway_settings.project_messenger_id','project_messengers.project_id'])
            ->join('gateway_settings as G2','gateway_settings.project_messenger_id','=','G2.project_messenger_id')
            ->join('project_messengers','project_messengers.id','=','gateway_settings.project_messenger_id')
            ->where([
                'gateway_settings.field_value'=>$value,
                'gateway_settings.field_name'=>$field
            ])->get();
    }
}