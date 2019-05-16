<?php
namespace App\Http\Controllers;

use App\Models\ProjectKey;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class ProjectKeyController extends Controller
{
    public function create($projectId,Request $request) {
        //only for admin and managers
        if(!$request->user()->hasAnyRole(['admin','managers','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($projectId)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if(ProjectKey::where('project_id',$projectId)->count() >= 10){
            return response()->json([
                "success"=>false,
                "errors"=>["access_key_limit"=>"Количество ключей в проекте не может превышать 10."]
            ],400);
        }

        $token = Uuid::uuid1()->toString();

        $projectKey = new ProjectKey();
        $projectKey->access_key = $token;
        $projectKey->project_id = $projectId;
        $projectKey->user_id = $request->user()->id;

        if($projectKey->save()){
            $projectKey->setAttribute('user',$projectKey->user);
           return response()->json([
               "success"=>true,
               "ProjectKey"=>$projectKey
           ]);
        }
        return response()->json([
            "success"=>false,
        ],400);
    }

    public function update($id,Request $request) {
        //only for admin and managers
        if(!$request->user()->hasAnyRole(['admin','managers','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $status = $request->get('inactive');
        $projectKey = ProjectKey::findOrFail($id);

        if($request->user()->hasRole('client') && !$request->user()->ownProject($projectKey->project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $projectKey->inactive = $status;

        if($projectKey->save()){
            return response()->json([
                "success"=>true,
                "ProjectKey"=>$projectKey
            ]);
        }

        return response()->json([
            "success"=>false,
        ],400);
    }

    public function destroy($id,Request $request) {
        //only for admin and managers
        if(!$request->user()->hasAnyRole(['admin','managers','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            $projectKey = ProjectKey::findOrFail($id);

            if($request->user()->hasRole('client') && !$request->user()->ownProject($projectKey->project_id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }
            $projectKey->delete();
            return response()->json([
                "success"=>true,
                "id"=>$id
            ]);
        }
        catch (\Exception $e){
            return response()->json([
                "success"=>false,
                "id"=>$id,
                "errors"=>$e->getMessage()
            ],400);
        }
    }
}