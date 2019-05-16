<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GatewaySubscribe extends Model
{
    use SoftDeletes;

    const ACTION_TEMPLATE = 'template';
    const ACTION_PHONE = 'phone';
    const ACTION_SMS = 'sms';

    protected $fillable = [
        'project_messenger_id',
        'callback',
        'action',
        'template_id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function projectMessenger(){
        return $this->hasOne(ProjectMessenger::class,'id','project_messenger_id');
    }

}
