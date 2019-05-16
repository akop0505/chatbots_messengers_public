<?php

namespace App\Models;

use App\Repositories\ProjectRepository;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Project extends Model implements ProjectRepository
{
    use SoftDeletes;


    protected $fillable = [
        'name',
        'web_site',
        'client_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $appends = [
        'removable'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($project) {
            foreach ($project->projectMessengers as $pm) {
                $pm->delete();
            }
            foreach ($project->projectKeys as $pk) {
                $pk->delete();
            }
            foreach ($project->events as $event) {
                $event->delete();
            }
            foreach ($project->receivers as $receiver) {
                $receiver->delete();
            }
            foreach ($project->menus as $menu) {
                $menu->delete();
            }
            foreach ($project->parameters as $parameter) {
                $parameter->delete();
            }
            foreach ($project->templates as $template) {
                $template->delete();
            }
            foreach ($project->weekDays as $day) {
                $day->delete();
            }
        });
    }

    private $relationTablesClass = [
        Template::class,
        Menu::class,
        Event::class,
        Receiver::class
    ];

    public function __construct(array $attributes = [])
    {
        if(request()->route()->getName() !== 'projects.index'){
            $this->appends = array_filter($this->appends, function($v){
                return ($v !== 'removable');
            });
        };
        parent::__construct($attributes);
    }

    public function getRemovableAttribute()
    {
        foreach ($this->relationTablesClass as $relClass) {
            $count = $relClass::where(['project_id'=>$this->id])->count();
            if($count){
                return 0;
            }
        }
        return 1;
    }

    public function projectMessengers(){
        return $this->hasMany(ProjectMessenger::class,'project_id','id');
    }

    public function events(){
        return $this->hasMany(Event::class,'project_id','id');
    }


    public function client(){
        return $this->belongsTo(User::class,'client_id','id');
    }

    public function receivers(){
        return $this->hasMany(Receiver::class,'project_id','id');
    }

    public function menus(){
        return $this->hasMany(Menu::class,'project_id','id');
    }

    public function parameters(){
        return $this->hasMany(Parameter::class,'project_id','id');
    }

    public function weekDays(){
        return $this->hasMany(WeekDay::class,'project_id','id');
    }

    public function templates(){
        return $this->hasMany(Template::class,'project_id','id');
    }

    public function projectKeys(){
        return $this->hasMany(ProjectKey::class,'project_id','id');
    }
}