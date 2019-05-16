<?php
namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use DB;

class UserController extends Controller
{
    /**
     * get all users using pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;
        $usersQuery = User::query()
            ->with(['role'=>function($query){
                return $query->select(['id','name','description']);
            },'parent'=>function($query){
                $query->select(['id','first_name','last_name','father_name','role_id'])
                    ->with(['role'=>function($rq){
                        $rq->select(['id','name']);
                    }]);
            }]);
        if($request->user()->hasRole('client')){
            $usersQuery->where(['parent_id'=>$request->user()->id])->orWhere(['id'=>$request->user()->id]);
        }
        $totalRows = $usersQuery->count();
        $users = $usersQuery
            ->offset($page*$limit)
            ->limit($limit)
            ->get();
        return response()->json([
            'Users'=>$users,
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
        $user = $event = User::find($id);
        if($request->user()->hasRole('client') && ($request->user()->id != $id && $request->user()->id != $user->parent_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        return response()->json([
            'User'=>$user,
            'success'=>true
        ],200);
    }

    /**
     * create a new user
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            $user = new User();
            $user->fill($data);
            if($request->user()->hasRole('client')){
                $clientRole = Role::select('id')->where(['name'=>'client'])->first();
                $user->role_id = $clientRole->id;
                $user->parent_id = $request->user()->id;
            }else{
                $user->role_id = $request['role_id'];
                if($data['parent_id']){
                    $user->parent_id = $data['parent_id'];
                }else{
                    $user->parent_id = $request->user()->id;
                }
            }
            $user->password = bcrypt($request->get('password'));
            $user->save();

            return response()->json([
                'User'=>$user,
                'success'=>true
            ],200);

        }catch (\Exception $e) {
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    /**
     * update the user by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id,UpdateUserRequest $request) {
        //only for admin
        $user = User::findOrFail($id);

        if(!$request->user()->hasAnyRole(['admin']) && $request->user()->id != $id){
            if($request->user()->hasRole(['client']) && ($request->user()->id != $id && $user->parent_id != $request->user()->id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }
        }

        try{
            $user->fill($request->all());
            if($request->user()->hasRole('client')){
                $user->parent_id = $request->user()->id;
            }
            if($request->user()->hasAnyRole(['admin'])){
                if($request->get('role_id')){
                    $user->role_id = $request->get('role_id');
                }
                if($request->get('parent_id')){
                    $user->parent_id = $request->get('parent_id');
                }
            }

            if($request->get('password')){
                $user->password = bcrypt($request->get('password'));
            }
            $user->save();
            return response()->json([
                'User'=>$user,
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
     * delete the user by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        if($request->user()->id == $id){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $user->delete();

            DB::commit();
            return response()->json([
                'id'=>$id,
                'success'=>true
            ],200);

        }catch (\Exception $e){
            DB::rallBack();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    public function getClients(Request $request){
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $clients = User::query()
            ->distinct()
            ->select(['users.id','users.first_name','users.last_name','users.father_name','users.role_id'])
            ->join('roles', function ($join) use($request) {
                $join->on('users.role_id', '=', 'roles.id')
                    ->where(['roles.name'=>'client'])->orWhere(['users.id'=>$request->user()->id]);
            })->get();
        return response()->json([
            'Clients'=>$clients,
            'success'=>true
        ],200);
    }

    public function getManagers(Request $request){
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $managers = User::query()
            ->distinct()
            ->select(['users.id','users.first_name','users.last_name','users.father_name','users.role_id'])
            ->join('roles', function ($join) use($request) {
                $join->on('users.role_id', '=', 'roles.id')
                    ->where(['roles.name'=>'manager']);
            });
        if( $request->user()->hasAnyRole(['client','manager']) ){
            $parent_id = $request->user()->hasRole('manager') ? $request->user()->parent_id : $request->user()->id;
            $managers->where(['users.parent_id'=>$parent_id]);
        }
        $managers = $managers->get();
        return response()->json([
            'Managers'=>$managers,
            'success'=>true
        ],200);
    }
}