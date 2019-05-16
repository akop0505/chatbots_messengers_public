<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receiver extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'description'
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'removable'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($menu) {
            foreach ($menu->templates as $template) {
                $template->delete();
            }
        });
    }

    public function templates(){
        return $this->hasMany(Template::class,'menu_id','id');
    }

    private $relationTablesClass = [
        Template::class
    ];

    public function project(){
        return $this->belongsTo(Project::class,'project_id','id');
    }

    public function getRemovableAttribute()
    {
        foreach ($this->relationTablesClass as $relClass) {
            $count = $relClass::where(['event_id'=>$this->id])->count();
            if($count){
                return 0;
            }
        }
        return 1;
    }
}