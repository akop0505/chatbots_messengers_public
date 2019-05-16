<?php

namespace App\Http\Controllers;


use App\Http\Requests\EventRequest\StoreEventsRequest;
use App\Http\Requests\EventRequest\UpdateEventsRequest;
use App\Http\Resources\Events\EventCollection;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;
        $eventsQuery = Event::query()
            ->with('project')
            ->orderBy('project_id');

        if($request->user()->hasRole('client')){
            $eventsQuery
                ->select(['events.*'])
                ->join('projects','projects.id','=','events.project_id')->where(['client_id'=>$request->user()->id]);
        }

        $totalRows = $eventsQuery->count();
        $events = $eventsQuery
            ->offset($page*$limit)
            ->limit($limit)
            ->get();
        return response()->json([
            'Events'=>new EventCollection($events),
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    /**
     * get event by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request){
        //only for admin manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $event = Event::findOrFail($id);
            if($request->user()->hasRole('client') && !$request->user()->ownProject($event->project_id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }

            return response()->json([
                'Event'=>$event,
                'success'=>true
            ],200);
        }catch(\Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    /**
     *
     * create a new event
     *
     * @param StoreEventsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventsRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        if($request->user()->hasRole('client') && !$request->user()->ownProject($request->get('project_id'))){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            $event = new Event();
            $event->fill($data);
            $event->save();
            return response()->json([
                'Event'=>$event,
                'success'=>true
            ],200);
        }catch ( \Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    /**
     *
     * update event by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id,UpdateEventsRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            $data = $request->all();
            $event = Event::find($id);

            if($request->user()->hasRole('client') && !($request->user()->ownProject($data['project_id']) && $request->user()->ownProject($event->project_id))){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }

            $event->fill($data);
            $event->save();
            return response()->json([
                'Event'=>$event,
                'success'=>true
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    /**
     *
     * delete event by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            $event = Event::findOrFail($id);
            if($request->user()->hasRole('client') && !$request->user()->ownProject($event->project_id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }
            $event->delete();

            return response()->json([
                'id'=>$id,
                'success'=>true
            ],200);

        }catch (\Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }
    public function eventsToSelect(Request $request) {
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $project_id  = $request->get('project_id');

        if($request->user()->hasRole('client') && !$request->user()->ownProject($project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $eventsQ = Event::query()->select(['id','name']);
        if(isset($project_id) && !empty($project_id)){
            $eventsQ->where(['project_id'=>$project_id]);
        }
        $events = $eventsQ->get();
        return response()->json([
            'Events'=>$events,
            'success'=>true
        ],200);
    }
}