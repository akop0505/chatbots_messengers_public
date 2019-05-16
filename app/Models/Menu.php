<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'callback_url'
    ];

    protected $appends = [
        'removable'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($menu) {
            foreach ($menu->menuItems as $menuItem) {
                $menuItem->delete();
            }
            foreach ($menu->templates as $template) {
                $template->delete();
            }
        });
    }

    private $relationTablesClass = [
        Template::class
    ];

    public function menuItems(){
        return $this->hasMany(MenuItem::class,'menu_id','id');
    }
    public function presentReplies(){
        return $this->hasMany(PresentReply::class,'menu_id','id');
    }
    public function templates(){
        return $this->hasMany(Template::class,'menu_id','id');
    }
    public function project(){
        return $this->belongsTo(Project::class,'project_id','id');
    }

    public function getRemovableAttribute()
    {
        foreach ($this->relationTablesClass as $relClass) {
            $count = $relClass::where(['menu_id'=>$this->id])->count();
            if($count){
                return 0;
            }
        }
        return 1;
    }
}