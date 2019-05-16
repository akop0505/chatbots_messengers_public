<?php

namespace App\Models;

use App\Repositories\PresentReplyRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresentReply extends Model implements PresentReplyRepository
{
    use SoftDeletes;

    protected $table = 'present_replies';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'point',
        'text',
        'default',
        'menu_id'
    ];

    public static function saveModel($data,$id = null){
        $model = new self();
        if( $id !== null){
            $model = self::findOrFail($id);
        }
        $model->fill($data);
        $model->save();
        return $model;
    }
    public function getRandomReplyMessage($point, $menuId)
    {
        return $this->inRandomOrder()->where(['point'=>$point,'menu_id'=>$menuId])->first();
    }
}
