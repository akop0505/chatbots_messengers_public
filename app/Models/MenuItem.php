<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;

    const REPLY_PRESENT = 'present';
    const REPLY_TEMPLATE = 'template';
    const REPLY_LOGIC_TEMPLATE = 'logic_template';
    const REPLY_NONE = 'none';

    protected $fillable = [
        'menu_id',
        'name',
        'point',
        'callback_url',
        'auto_reply',
        'reply_type',
        'template_id',
        'false_template_id',
        'present_replay_id',
        'default'
    ];

    protected $dates = ['deleted_at'];

    public function Menu(){
        return $this->hasOne(Menu::class,'menu_id','id');
    }

    public static function saveModel($data,$id = null){
        $model = new self();
        if( $id !== null){
            $model = self::findOrFail($id);
        }
        $model->fill($data);
        $model->save();
        return $model;
    }
}