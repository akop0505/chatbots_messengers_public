<?php

namespace App\Models;

use App\Repositories\UserChatInfoRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use AppFacebookService;
use Illuminate\Support\Facades\Log;

class UsersChatInfo extends Model implements  UserChatInfoRepository
{
    protected $table = "users_chat_info";

    protected $fillable = [
        'project_id',
        'chat_id',
        'bot_id',
        'user',
        'bot',
        'channel'
    ];

    protected $appends = [
        'not_seen'
    ];

    public function getNotSeenAttribute()
    {
        return $this->getNotSeenMessagesCount();
    }

    public function createNewViberUserInfo($user){
        $row = $this->query()->where([
            'project_id'=>$user['project_id'],
            'chat_id'=>$user['chat_id'],
            'bot_id'=>$user['bot_id'],
            'channel'=>$user['channel']
        ])->first();
        if(!$row){
            return $this->create($user);
        }
        return $row;
    }

    public function getViberUsersInfo($token,$projectId = null){
        $query = $this->query()->where([
                "bot_id"=>$token,
                "channel"=>"viber"
            ]);
        if($projectId !== null ){
            $query->where(['project_id'=>$projectId]);
        }
        return $query->get();
    }

    public function getFbUser($appId,$userId,$projectId){
        return  $this->query()->where([
            "project_id"=>$projectId,
            "chat_id"=>$userId,
            "bot_id"=>$appId,
            "channel"=>'fb',
        ])->first();
    }
    public function getFbUsers($appId){
        return  $this->query()->where([
//            "bot"=>$bot,
            "bot_id"=>$appId,
            "channel"=>'fb',
        ])->get();
    }

    public function createNewFbUserInfo($data){
        $row = $this->getFbUser($data['bot_id'],$data['chat_id'],$data['project_id']);
        if(!$row){
            $userInfo = AppFacebookService::getUserInfo($data['token'],$data['chat_id'],true);
            $data['channel'] = 'fb';
            $data['user'] = json_encode($userInfo,JSON_UNESCAPED_UNICODE);
            $data['bot'] = json_encode($data['bot'],JSON_UNESCAPED_UNICODE);
            return $this->create($data);
        }
        return $row;
    }

    public function getAll($filter = [],$duplicates = true){
        $query = $this->query()
            ->select('users_chat_info.*')
            ->join('projects','users_chat_info.project_id','=','projects.id','inner')
            ->join('users','projects.client_id','=','users.id','inner')
            ->where([
                'users.id'=>Auth::user()->id,
                //['channel','<>','fb']
            ])
            ->whereNull('projects.deleted_at');
        if(count($filter)){
            if(isset($filter['channel']) && !empty($filter['channel'])){
                $query->where(['users_chat_info.channel'=>$filter['channel']]);
            }
            if(isset($filter['project']) && !empty($filter['project'])){
                $query->where(['users_chat_info.project_id'=>$filter['project']]);
            }
            if(isset($filter['search']) && !empty($filter['search'])){
                $search = $filter['search'];
                $query->where(function($subQuery) use($search) {
                    $subQuery->where('users_chat_info.user','like','%'.$search.'%')
                    ->orWhere('users_chat_info.bot','like','%'.$search.'%');
                });
            }
            if(isset($filter['not_seen']) && $filter['not_seen'] === "true"){
                $query
                    ->join('answerable_messages',function($join){
                        $join->on('answerable_messages.chat_id','=','users_chat_info.chat_id');
                        $join->on('answerable_messages.channel','=','users_chat_info.channel');
                        $join->on('answerable_messages.bot_username','=','users_chat_info.bot_id');
                    })
                    ->where(['answerable_messages.seen'=>false])
                    ->whereNotIn('answerable_messages.state',['answered','waiting','bot_simple']);
                ;

            }
        }
        $result = $query->get();
        if($duplicates === false){
            return $result->unique('chat_id');
        }
        return $result;
    }

    public function getNotSeenMessagesCount(){
        $query = AnswerableMessage::query()
            ->select(["id"])
            ->where([
                "seen"=>false,
                "channel" =>$this->channel,
                "chat_id"=>$this->chat_id,
                "bot_username"=>$this->bot_id
            ])
            ->whereNotIn('state',['answered','waiting','bot_simple']);
        return $query->count();
    }
}
