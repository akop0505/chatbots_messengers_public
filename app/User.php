<?php

namespace App;

use App\Http\Traits\CanTrait;
use App\Models\Project;
use App\Models\Role;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements UserRepository
{
    use Notifiable, HasApiTokens, CanTrait,SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'first_name',
        'last_name',
        'father_name',
        'username',
        'phone',
        'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'removable'
    ];

    private $relationTablesClass = [
        Project::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($user) {
            foreach ($user->children as $child) {
                $child->delete();
            }
            foreach ($user->project as $project) {
                $project->delete();
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class,'role_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class,'parent_id','id');
    }

    public function children()
    {
        return $this->hasMany(self::class,'parent_id','id');
    }

    public function project(){
        return $this->hasMany(Project::class,'client_id','id');
    }

    public function getProjectByAccessKey($accessKey) {
        return Project::query()
            ->select(['projects.*'])
            ->join('project_keys','projects.id','=','project_keys.project_id')
            ->where([
                'project_keys.access_key'=>$accessKey,'project_keys.inactive'=>0])
            ->first();
    }

    public function getRemovableAttribute()
    {
        foreach ($this->relationTablesClass as $relClass) {
            $field = "user_id";
            if($relClass === "App\Models\Project"){
                $field = "client_id";
            }
            $count = $relClass::where([$field=>$this->id])->count();
            if($count){
                return 0;
            }
        }
        return 1;
    }
}
