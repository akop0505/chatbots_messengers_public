<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest\StoreProjectRequest;
use App\Http\Requests\ProjectRequest\UpdateProjectRequest;
use App\Models\GatewaySetting;
use App\Models\GatewaySubscribe;
use App\Models\Project;
use App\Models\ProjectKey;
use App\Models\ProjectMessenger;
use AppTelegramService;
use AppViberService;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;

        $projectsQuery = Project::query()
            ->with(['projectMessengers'=>function($query){
                return $query
                    ->with(['permission','gateway'=>function($qm){
                        $qm->with('messenger');
                    }]);
            },'client']);
        if($request->user()->hasRole('client')){
            $projectsQuery->where(['client_id'=>$request->user()->id]);
        }
        $totalRows = $projectsQuery->count();
        $projects = $projectsQuery->offset($page*$limit)
            ->limit($limit)
            ->get();
        return response()->json([
            'Projects'=>$projects,
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    public function show($id,Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $project = Project::query()
            ->with(['projectMessengers' => function ($query) {
                return $query
                    ->with(['permission', 'subscribe', 'gatewaySettings', 'gateway' => function ($qm) {
                        $qm->with('messenger');
                    }]);
            },
                'client',
                'projectKeys' => function ($query) {
                    return $query->with(['user' => function ($uQuery) {
                        return $uQuery->select(['id', 'first_name', 'last_name', 'father_name']);
                    }]);
                }])
            ->where(['id' => $id])
            ->first();

        $response = [];
        if($project){
            $response = $project->toArray();
            $response['ProjectMessengers'] = $response['project_messengers'];
            $response['ProjectKeys'] = $response['project_keys'];
            unset($response['project_messengers']);
            unset($response['project_keys']);
            foreach ($response['ProjectMessengers'] as $key=>&$value){
                $value['GatewaySettings'] = $value['gateway_settings'];
                unset($value['gateway_settings']);
            }
        }

        return response()->json([
            'Project'=>$response,
            'success'=>true
        ],200);
    }

    public function store(StoreProjectRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $data = $request->all();
        $responseData = [];
        try{
            DB::beginTransaction();
            $project = new Project();
            $project->fill($data);

            if($request->user()->hasRole('client')){
                $project->setAttribute('client_id',$request->user()->id);
            }

            $project->save();

            $responseData["Project"] = $project->toArray();

            $responseData["Project"]["ProjectMessengers"] = [];

            foreach ($data['ProjectMessengers'] as $pMessenger) {
                $projectMessenger =  new ProjectMessenger();
                $projectMessenger->fill($pMessenger);
                $projectMessenger->project_id = $project->id;
                $projectMessenger->save();

                if($pMessenger['subscribe']['action']) {
                    $gatewaySubscribe = new GatewaySubscribe();
                    $gatewaySubscribe->fill($pMessenger['subscribe']);
                    $gatewaySubscribe->project_messenger_id = $projectMessenger->id;
                    $gatewaySubscribe->save();
                }

                array_push($responseData["Project"]["ProjectMessengers"],$projectMessenger);
                if($projectMessenger->gateway_id){
                    foreach ($pMessenger['GatewaySettings'] as $setting) {
                        $gatewaySetting = new GatewaySetting();
                        $gatewaySetting->fill($setting);
                        $gatewaySetting->project_messenger_id = $projectMessenger->id;
                        $gatewaySetting->save();
                    }
                    if($projectMessenger->gateway->isChannel('telegram')){
                        AppTelegramService::setWebHook($projectMessenger->getGatewaySettings('token'));
                    }
                    if($projectMessenger->gateway->isChannel('viber')){
                        AppViberService::setWebHook($projectMessenger->getGatewaySettings('token'));
                    }
                }
            }
            DB::commit();
            return response()->json([
                'Project'=>$responseData['Project'],
                'success'=>true
            ],200);
        }catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    public function update($id, UpdateProjectRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $dataProjectMessengers = $request->get('ProjectMessengers');
        $needToUpdateKeys = [];
        $needToUpdate = [];
        $needToCreate = [];

        foreach($dataProjectMessengers as $projectMessenger){
            if(isset($projectMessenger['id']) && !empty($projectMessenger['id'])){
                $needToUpdateKeys[] =$projectMessenger['id'];
                $needToUpdate[] = $projectMessenger;
            }else{
                $needToCreate[] = $projectMessenger;
            }
        }

        $projectResult = [];
        $projectMessengersResult = [];
        try{
            DB::beginTransaction();
            $project = Project::findOrFail($id);

            $projectMessengersKey = ProjectMessenger::select('id')->where(['project_id'=>$id])->get();
            $projectMessengersArray = $projectMessengersKey->map(function($item) {
                return $item['id'];
            });

            /*Delete the ProjectMessengers which has not in new request data*/
            $needToRemove = array_diff($projectMessengersArray->toArray(),$needToUpdateKeys);
            if(count($needToRemove)){
                GatewaySetting::query()->whereIn('project_messenger_id',$needToRemove)->delete();
                foreach($needToRemove as $toRemove){
                    ProjectMessenger::where(['id'=>$toRemove])->delete();
                }
            }

            /* Update the ProjectMessengers */
            foreach ($needToUpdate as $toUpdate){
                $projectMessenger = ProjectMessenger::findOrFail($toUpdate['id']);
                $projectMessenger->fill($toUpdate);
                $projectMessenger->save();

                $gatewaySubscribe = $projectMessenger->subscribe;
                if($toUpdate['subscribe']['action']) {
                    if( ! $gatewaySubscribe) {
                        $gatewaySubscribe = new GatewaySubscribe();
                        $gatewaySubscribe->project_messenger_id = $projectMessenger->id;
                    }
                    $gatewaySubscribe->fill($toUpdate['subscribe']);
                    $gatewaySubscribe->save();
                }else if($gatewaySubscribe){
                    $gatewaySubscribe->delete();
                }

                if($projectMessenger->gateway_id){
                    $projectMessenger->gatewaySettings()->delete();
                    foreach ($toUpdate['GatewaySettings'] as $setting) {
                        $gatewaySetting = new GatewaySetting();
                        $gatewaySetting->fill($setting);
                        $gatewaySetting->project_messenger_id = $projectMessenger->id;
                        $gatewaySetting->save();
                    }
                }
                if($projectMessenger->gateway->isChannel('telegram')){
                    AppTelegramService::setWebHook($projectMessenger->getGatewaySettings('token'));
                }
                if($projectMessenger->gateway->isChannel('viber')){
                    AppViberService::setWebHook($projectMessenger->getGatewaySettings('token'));
                }
                $projectMessengersResult[] = $projectMessenger;
            }

            /* Create new ProjectMessengers */
            foreach ($needToCreate as $toCreate){
                $newProjectMessenger = new ProjectMessenger();
                $newProjectMessenger->fill($toCreate);
                $newProjectMessenger->setAttribute('project_id',$id);
                $newProjectMessenger->save();

                if(! $toCreate['subscribe']['action']) {
                    $gatewaySubscribe = new GatewaySubscribe();
                    $gatewaySubscribe->project_messenger_id = $newProjectMessenger->id;
                    $gatewaySubscribe->fill($toCreate['subscribe']);
                    $gatewaySubscribe->save();
                }

                $projectMessengersResult[] = $newProjectMessenger;
                foreach ($toCreate['GatewaySettings'] as $setting) {
                    $gatewaySetting = new GatewaySetting();
                    $gatewaySetting->fill($setting);
                    $gatewaySetting->project_messenger_id = $newProjectMessenger->id;
                    $gatewaySetting->save();
                }
                if($newProjectMessenger->gateway->isChannel('telegram')){
                    AppTelegramService::setWebHook($newProjectMessenger->getGatewaySettings('token'));
                }
                if($projectMessenger->gateway->isChannel('viber')){
                    AppViberService::setWebHook($projectMessenger->getGatewaySettings('token'));
                }
            }

            $project->fill($request->all());
            $project->save();
            DB::commit();

            $projectResult = $project->toArray();
            $projectResult["ProjectMessengers"] = $projectMessengersResult;

            return response()->json([
                'Project'=>$projectResult,
                'success'=>true
            ],200);

        }
        catch (ModelNotFoundException $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    'No record found for id `'.$id.'`'
                ]
            ],400);
        }
        catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }


    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();
            $project = Project::findOrFail($id);
            ProjectMessenger::where(['project_id'=>$id])->delete();
            ProjectKey::where(['project_id'=>$id])->delete();

            $project->delete();
            DB::commit();
            return response()->json([
                'id'=>$id,
                'success'=>true
            ],200);
        }
        catch (ModelNotFoundException $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    'No record found for id `'.$id.'`'
                ]
            ],400);
        }
        catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    public function projectsToSelect(Request $request){
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $projectsQuery = Project::query()->select(['id','name']);

        if($request->user()->hasRole('client')){
            $projectsQuery->where(['client_id'=>$request->user()->id]);
        }

        return response()->json([
            'Projects'=>$projectsQuery->get(),
            'success'=>true
        ],200);
    }

    public function projectsCount(Request $request){
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $projectsQuery = Project::query();

        if($request->user()->hasRole('client')){
            $projectsQuery->where(['client_id'=>$request->user()->id]);
        }

        return response()->json([
            'count'=>$projectsQuery->count(),
            'success'=>true
        ],200);
    }

    public function saveTimezone($project_id, Request $request){
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            DB::beginTransaction();
            $project = Project::findOrFail($project_id);
            $project->timezone = $request->get('timezone');
            $project->save();
            DB::commit();

            return response()->json([
                'success'=>true
            ],200);

        }
        catch (ModelNotFoundException $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    'No record found for id `'.$project_id.'`'
                ]
            ],400);
        }
        catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }
}