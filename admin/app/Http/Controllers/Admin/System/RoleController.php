<?php

namespace App\Http\Controllers\Admin\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.system.role.index');
    }

    public function getData(Request $request)
    {
        $model = Role::select(['id', 'name', 'created_at']);
        $total = $model->count(); //总数
        $limit = $request->has('limit') ? $request->limit : 20;
        $page = $request->has('page') ? ($request->page - 1) * $limit : 0;
        $data = $model->offset($page)->limit($limit)->get();
        return json_encode(['code' => 0, 'data' => $data, 'count' => $total]);
    }

    public function add(Request $request)
    {
        $res = Role::create([
            'name' => $request->name
        ]);
        return $res? $this->responseData(200, '添加成功'):$this->responseData(400, '添加失败');
    }

    public function update(Request $request)
    {
        $res = Role::where('id', $request->id)->update([
            'name' => $request->name
        ]);
        return $res? $this->responseData(200, '添加成功'):$this->responseData(400, '添加失败');
    }

    public function del(Request $request)
    {
        $res = Role::where('id', $request->id)->delete();
        return $res? $this->responseData(200, '删除成功'):$this->responseData(400, '删除失败');
    }

    public function showPermissions(Request $request, $id)
    {
        return view('admin.system.role.permission');
    }

    public function permissionData(Request $request)
    {
        $role = Role::where('id', $request->id)->select()->first();
        $permissions = $role->getAllPermissions();
        if (!$permissions->isEmpty()) {
            $permissions = array_column($permissions->toArray(), 'id');
        } else {
            $permissions = [];
        }
        // return $permissions;
        $data = Permission::select([
            'id', 'pid as parentId', 'name as title','route'
        ])->get();
        $data->map(function($query) use ($permissions){
            if (in_array($query->id, $permissions)) {
                $query->checkArr = [["type" =>  "0", "checked" => "1"]];
            } else {
                $query->checkArr = [["type" =>  "0", "checked" => "0"]];
            }
            return $query;
        });
        $data = getTree($data);
        return [
            "status" => [
                "code" => 200,
                "message" => 'success'
            ],
            "data" => $data

        ];
    }

    public function changePermission(Request $request)
    {
        DB::beginTransaction();
        try{
            $role = Role::where('id', $request->id)->first();
            $permission = array_column(json_decode($request->permissions), 'id');
            $role->syncPermissions($permission);  
             
            DB::commit();
            return $this->responseData(200, '更新成功');

        } catch(\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
}
