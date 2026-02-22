<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.system.permission.index');
    }

    

    public function getData()
    {
        $data = Permission::select([
            'id', 'pid as parentId', 'name as title','route'
        ])->get();
        $data = getTree($data);
        return [
            "status" => [
                "code" => 200,
                "message" => 'success'
            ],
            "data" => $data

        ];
    }

    public function add(Request $request)
    {
        try {
            $res = Permission::create([
                'name' => $request->context,
                'pid' => $request->parentId??0,
                'route' => $request->route??''
            ]);
            return $res ? $this->responseData(200, '添加成功',[ "id" => $res->id]):$this->responseData(400, '添加失败');
        } catch (\Exception $e) {
            llog($e);
            return $this->responseData(400, $e->getMessage());
        }
        
        
    }

    public function update(Request $request)
    {
        $res = Permission::where('id', $request->nodeId)->update([
            'name' => $request->context,
            'route' => $request->route,
            'updated_at' => nowDate()
        ]);
        return $res?$this->responseData(200, '修改成功'):$this->responseData(400, '修改失败');
    }

    public function del(Request $request)
    {
        $res = Permission::where('id', $request->nodeId)->delete();
        return $res?$this->responseData(200, '删除成功'):$this->responseData(400, '删除失败');

    }

    public function getItem(Request $request)
    {

        $data = Permission::where('id', $request->nodeId)->first();
        return $this->responseData(200, 'success', ['route' => $data->route]);
    }

}
