<?php

namespace App\Http\Controllers;

use App\Http\Requests\TemplateRequest\StoreTemplateRequest;
use App\Http\Resources\Templates\TemplateCollection;
use App\Models\Template;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        //only for admin and manager
        if (!$request->user()->hasAnyRole(['admin', 'manager','client'])) {
            return response()->json(array_merge([
                'success' => false], __('messages.role_error')), 403);

        }
        //$project_id = $request->get('project_id');

        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;
        $templatesQuery = Template::query()
            ->with([
                'language'=>function($lng){
                    return $lng->select(['id','code','name']);
                },
                'event'=>function($ev){
                        return $ev->select(['id','name']);
                    },
                'project'=>function($p){
                        return $p->select(['id','name']);
                    },
                'receiver'=>function($r){
                        return $r->select(['id','name']);
                    },
                'menu'=>function($m){
                        return $m->with(['menuItems'=>function($mi){return $mi->select(['id','menu_id','point','name']);}]);
                    }
                ]);
//            ->where(['project_id'=>$project_id]);
        if ($request->user()->hasRole('client')) {
            $templatesQuery->select(['templates.*']);
            $templatesQuery
                ->join('projects','projects.id','=','templates.project_id')
                ->where(['projects.client_id'=>$request->user()->id]);
        }
        $totalRows = $templatesQuery->count();
        $templates = $templatesQuery
            ->offset($page*$limit)
            ->limit($limit)->get();
        return response()->json([
            'Templates'=>new TemplateCollection($templates),
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    public function getTemplatesToSelect(Request $request){
        if (!$request->user()->hasAnyRole(['admin', 'manager','client'])) {
            return response()->json(array_merge([
                'success' => false], __('messages.role_error')), 403);

        }
        $templatesQuery = Template::query()->select(['id','name','project_id']);

        if ($request->user()->hasRole('client')) {
            $templatesQuery->select(['templates.*']);
            $templatesQuery
                ->join('projects','projects.id','=','templates.project_id')
                ->where(['projects.client_id'=>$request->user()->id]);
        }
        $filters  = $request->all();
        if(count($filters)){
            $templatesQuery->where($filters);
        }
        $templates = $templatesQuery->get();
        return response()->json([
            'Templates'=>$templates,
            'success'=>true
        ],200);
    }

    public function show($id,Request $request)
    {
        //only for admin and manager
        if (!$request->user()->hasAnyRole(['admin', 'manager','client'])) {
            return response()->json(array_merge([
                'success' => false], __('messages.role_error')), 403);

        }
        $templates = Template::findOrFail($id);

        return response()->json([
            'Template'=>$templates,
            'success'=>true
        ],200);
    }

    public function store(StoreTemplateRequest $request)
    {
        //only for admin
        if (!$request->user()->hasAnyRole(['admin','client'])) {
            return response()->json(array_merge([
                'success' => false], __('messages.role_error')), 403);
        }
        try{
            $data = $request->all();
            $template = new Template($data);
            $template->save();
            return response()->json([
                'Template'=>$template,
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

    public function update($id,StoreTemplateRequest $request)
    {
        //only for admin
        if (!$request->user()->hasAnyRole(['admin','client'])) {
            return response()->json(array_merge([
                'success' => false], __('messages.role_error')), 403);

        }
        try{
            $template = Template::findOrFail($id);
            if($request->user()->hasRole('client') && !($request->user()->ownProject($request->get('project_id')) && $request->user()->ownProject($template->project_id))){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }

            $data = $request->all();
            $template->fill($data);
            $template->save();

            return response()->json([
                'Template'=>$template,
                'success'=>true
            ],200);
        }
        catch (\Exception $e){
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
        if (!$request->user()->hasAnyRole(['admin'])) {
            return response()->json(array_merge([
                'success' => false], __('messages.role_error')), 403);
        }
        try{
            $template = Template::findOrFail($id);
            $template->delete();
            return response()->json([
                'success'=>true,
                'id'=>$id
            ],200);
        }
        catch (\Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }
}