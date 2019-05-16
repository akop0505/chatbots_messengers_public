<?php
namespace App\Http\Controllers;

use App\Http\Requests\MenuRequest\StoreMenuRequest;
use App\Http\Requests\MenuRequest\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\PresentReply;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;

class MenuController extends Controller
{
    /**
     * get all menus with their menu items using pagination
     *
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

        $menuQuery = Menu::query()
            ->with(['menuItems'=>function($query){
                return $query->orderBy('point','asc');
            },'project'=>function($pRequest){
                $pRequest->select(['id','name']);
            },'presentReplies'=>function($query){
                return $query->orderBy('point','asc');
            }])
            ->orderBy('project_id');

        if($request->user()->hasRole('client')){
            $menuQuery->select('menus.*');
            $menuQuery
                ->join('projects','menus.project_id','=','projects.id')
                ->where(['projects.client_id'=>$request->user()->id]);
        }

        $totalRows = $menuQuery->count();
        $menu = $menuQuery->offset($page*$limit)
            ->limit($limit)
            ->get();

        return response()->json([
            'totalRows'=>$totalRows,
            'Menus'=>$menu,
            'success'=>true
        ],200);
    }

    /**
     *
     * get the menu by id with his menu items
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id,Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $menu = Menu::query()
            ->with(['menuItems'=>function($query){
                return $query->orderBy('point','asc');
            },'presentReplies'=>function($query){
                return $query->orderBy('point','asc');
            }])
            ->where(['id'=>$id])
            ->first();

        if($request->user()->hasRole('client') && !$request->user()->ownProject($menu->project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $menu = $menu->toArray();
        $menu['MenuItems'] = $menu['menu_items'];
        $menu['PresentReplies'] = $menu['present_replies'];
        unset($menu['menu_items']);
        unset($menu['present_replies']);
        return response()->json([
            'Menu'=>$menu,
            'success'=>true
        ],200);
    }

    /**
     *
     * create a new menu with his menu items
     *
     * @param StoreMenuRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreMenuRequest $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($request->get('project_id'))){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try {
            DB::beginTransaction();
            $menuData = $request->all();
            $menu = new Menu();
            $menu->fill($menuData);
            $menu->save();
            $responseData = $menu->toArray();
            $responseData["MenuItems"] = [];
            foreach ($menuData['MenuItems'] as $item){
                $menuItem = new MenuItem();
                $menuItem->fill($item);
                $menuItem->menu_id = $menu->id;
                $menuItem->save();
                $responseData["MenuItems"][] = $menuItem;
            }
            $responseData["PresentReplies"] = [];
            foreach ($menuData['PresentReplies'] as $pr){
                $presentReply = new PresentReply($pr);
                $presentReply->fill($pr);
                $presentReply->menu_id = $menu->id;
                $presentReply->save();
                $responseData["PresentReplies"][] = $presentReply;
            }
            DB::commit();
            return response()->json([
                'Menu'=>$responseData,
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

    /**
     * update the menu with his menu items
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, UpdateMenuRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $menu = Menu::findOrFail($id);

        /*if the user is client but he try to update the row which doesn't belongs to him */
        if($request->user()->hasRole('client') && !($request->user()->ownProject($request->get('project_id')) && $request->user()->ownProject($menu->project_id))){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $dataMenuItemsItems = $request->get('MenuItems');
        $needToUpdateKeys = [];
        $needToUpdate = [];
        $needToCreate = [];

        $dataPresentReplies = $request->get('PresentReplies');
        $needPresentRepliesToUpdateIdes = [];
        $needPresentRepliesToUpdate = [];
        $needPresentRepliesToCreate = [];

        foreach($dataMenuItemsItems as $menuItem){
            if(isset($menuItem['id']) && !empty($menuItem['id'])){
                $needToUpdateKeys[] = $menuItem['id'];
                $needToUpdate[] = $menuItem;
            }else{
                $needToCreate[] = $menuItem;
            }
        }

        foreach($dataPresentReplies as $pr){
            if(isset($pr['id']) && !empty($pr['id'])){
                $needPresentRepliesToUpdateIdes[] = $pr['id'];
                $needPresentRepliesToUpdate[] = $pr;
            }else{
                $needPresentRepliesToCreate[] = $pr;
            }
        }

        $menuResult = [];
        $menuItemsResult = [];
        $presentRepliesResult = [];

        try{
            DB::beginTransaction();

            $menuItemsKey = MenuItem::select('id')->where(['menu_id'=>$id])->get();
            $menuItemsKeysArray = $menuItemsKey->map(function($item) {
                return $item['id'];
            });

            /*Delete the MenuItems which has not in new request data*/
            $needToRemove = array_diff($menuItemsKeysArray->toArray(),$needToUpdateKeys);
            if(count($needToRemove)){
               foreach($needToRemove as $toRemove){
                   MenuItem::where(['id'=>$toRemove])->delete();
               }
            }

            /* Update the MenuItems */
            foreach ($needToUpdate as $toUpdate){
                $menuItemsResult[] = MenuItem::saveModel($toUpdate,$toUpdate['id']);
            }

            /* Create new MenuItems */
            foreach ($needToCreate as $toCreate){
                $toCreate['menu_id'] = $id;
                $menuItemsResult[] = MenuItem::saveModel($toCreate);
            }

            /* get All PresentReplies by menu_id */
            $presentRepliesIdes = PresentReply::select('id')->where(['menu_id'=>$id])->get();
            $presentRepliesIdesArray = $presentRepliesIdes->map(function($item) {
                return $item['id'];
            });

            /*Delete the PresentReplies which has not in new request data*/
            $needPresentRepliesToRemove = array_diff($presentRepliesIdesArray->toArray(),$needPresentRepliesToUpdateIdes);
            if(count($needPresentRepliesToRemove)){
                foreach($needPresentRepliesToRemove as $toRemove){
                    PresentReply::where(['id'=>$toRemove])->delete();
                }
            }

            /* Update the PresentReplies */
            foreach ($needPresentRepliesToUpdate as $toUpdate){
                $presentRepliesResult[] = PresentReply::saveModel($toUpdate,$toUpdate['id']);
            }

            /* Create new PresentReplies*/
            foreach ($needPresentRepliesToCreate as $toCreate){
                $toCreate['menu_id'] = $id;
                $presentRepliesResult[] = PresentReply::saveModel($toCreate);
            }



            $menu->fill($request->all());
            $menu->save();
            DB::commit();

            $menuResult = $menu->toArray();
            $menuResult["MenuItems"] = $menuItemsResult;

            return response()->json([
                'Menu'=>$menuResult,
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

    /**
     *
     * delete the menu with his menu items
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->hasRole('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();
            $menu = Menu::findOrFail($id);
            $menuItems = MenuItem::where(['menu_id'=>$id])->delete();
            if(!$menuItems){
                throw new ModelNotFoundException();
            }
            $menu->delete();
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menusToSelect(Request $request) {
        $project_id  = $request->get('project_id');
        $menusQ = Menu::query()->select(['id','name']);
        if(isset($project_id) && !empty($project_id)){
            $menusQ->where(['project_id'=>$project_id]);
        }
        $menus = $menusQ->get();
        return response()->json([
            'Menus'=>$menus,
            'success'=>true
        ],200);
    }
}