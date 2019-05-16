<?php

namespace App\Models;

use App\Repositories\TemplateRepository;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model implements TemplateRepository
{
    use SoftDeletes;

    protected  $fillable = [
        'project_id',
        'country',
        'name',
        'event_id',
        'receiver_id',
        'payment_type',
        'text',
        'menu_id',
    ];

    protected $appends = [
        'removable'
    ];

    private $relationTablesClass = [
        MenuItem::class,
        AnswerableMessage::class

    ];

    public function language(){
        return $this->belongsTo(Language::class,'country','code');
    }
    public function event(){
        return $this->belongsTo(Event::class,'event_id','id');
    }

    public function receiver(){
        return $this->belongsTo(Receiver::class,'receiver_id','id');
    }

    public function project(){
        return $this->belongsTo(Project::class,'project_id','id');
    }

    public function menu(){
        return $this->belongsTo(Menu::class,'menu_id','id');
    }

    public function getRemovableAttribute()
    {
        foreach ($this->relationTablesClass as $relClass) {
            $count = $relClass::where(['template_id'=>$this->id])->count();
            if($count){
                return 0;
            }
        }
        return 1;
    }

    public function getById($id)
    {
        return self::findOrFail($id);
    }

    public function getMenu(){
        return $this->menu()
            ->select(['id','name','callback_url'])
            ->with(['menuItems'])
            ->first();
    }

    public function getTemplatesProject($channel){
        return $this->project()->with(['projectMessengers'=>function($q) use($channel){
            $q->select('project_messengers.*')
                //->join('messengers','project_messengers.messenger_id','=','messengers.id')
                ->join('permissions','project_messengers.permission_id','=','permissions.id')
                ->join('gateways','project_messengers.gateway_id','=','gateways.id')
                ->join('messengers','gateways.messenger_id','=','messengers.id')
                ->where('permissions.alias','send')
                ->where('messengers.alias',$channel)
                ->whereNotNull('project_messengers.gateway_id');
        }])->first();
    }
}
