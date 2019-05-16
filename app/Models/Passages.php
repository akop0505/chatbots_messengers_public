<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passages extends Model
{
    use SoftDeletes;

    const DELETED_AT = 'closed_at';

    protected $fillable = [
        'sequence_id',
        'lead_id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['closed_at'];

    public function sequence(){
        return $this->belongsTo(Sequence::class,'sequence_id','id');
    }

    public function project(){
        return $this->sequence->project();
    }

    public function lead(){
        return $this->belongsTo(Lead::class,'lead_id','id');
    }

    public function actions(){
        return $this->hasMany(PassagesAction::class,'passage_id','id');
    }

    public static function boot() {
        parent::boot();

        static::created(function ( Passages $passage) {
            $tasks = $passage->sequence->options['tasks'];
            $weekDays = $passage->project->weekDays()->where(['enabled'=>1])->get();
            if( ! $weekDays){return false;}
            $day = 1; // по умолчанию задачи выставляются на следующий день
            foreach ($tasks as $task){
                $start_at = (new \DateTime());

                if($task['type'] === 'day'){ $day = $task['index']; continue;}
                $start_at->modify("+$day days");

                $start_at->setTimezone( new \DateTimeZone($passage->project->timezone) );

                // получаем рабочие часы текущего дня
                $weekDay = $weekDays->firstWhere('order', $start_at->format( 'N' ));
                if( ! $weekDay){
                    // если сегодня не рабочий день, ищем следующий рабочий день
                    static::findNextWorkDay($weekDays, $weekDay, $start_at);
                }

                if($task['wait']){ // установлен час исполнения задачи
                    $wait = explode( ':', $task['wait'] );
                    $weekDayTo = explode( ':', $weekDay->to );
                    $weekDayFrom = explode( ':', $weekDay->from );
                    // если задача выполнятеся вне рабочее время переносим на следующий рабочий день
                    if(
                        ($wait[0] > $weekDayTo[0] || ($wait[0] === $weekDayTo[0] && $wait[1] > $weekDayTo[1])) ||
                        ($wait[0] < $weekDayFrom[0] || ($wait[0] === $weekDayFrom[0] && $wait[1] < $weekDayFrom[1]))
                    ){
                        static::findNextWorkDay($weekDays, $weekDay, $start_at);
                        $weekDayFrom = explode( ':', $weekDay->from );
                        $start_at->setTime($weekDayFrom[0], $weekDayFrom[1]);
                    }else {
                        $start_at->setTime($wait[0], $wait[1]);
                    }
                }else{ // выставляем по умолчанию в начало рабочего дня задачи
                    $weekDayFrom = explode( ':', $weekDay->from );
                    $start_at->setTime($weekDayFrom[0], $weekDayFrom[1]);
                }

                (new PassagesAction([
                    'options' => $task['options'],
                    'type' => $task['type'],
                    'passage_id' => $passage->id,
                    'lead_id' => $passage->lead_id,
                    'project_id' => $passage->project->id,
                    'start_at' => $start_at->format("Y-m-d H:i:s"),
                    'status' => PassagesAction::STATUS_PENDING,
                ]))->save();
            }
        });
        static::deleted(function ( Passages $passage) {
            $passage->actions()->delete();
        });
    }

    private static function findNextWorkDay( $weekDays, &$weekDay, &$start_at ){
        for($i = 0; $i < 15; $i++){
            $start_at->modify("+1 days");
            $weekDay = $weekDays->firstWhere('order', $start_at->format('N'));
            if( $weekDay ){break;}
        }
    }
}
