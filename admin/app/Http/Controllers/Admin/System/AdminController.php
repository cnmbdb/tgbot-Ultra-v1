<?php

namespace App\Http\Controllers\Admin\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::pluck('name', 'id');
        return view('admin.system.admin.index', compact('roles'));
    }

    public function getData(Request $request)
    {
        $model = Admin::with([
            'roles:id,name'
        ])
        ->where(function($query) use ($request){
            if ( $request->has('name') && !empty($request->name )) {
                $query->where('name', $request->name);
            }        
        })->select(['id', 'name', 'status', 'created_at', 'white_ip']);
        $total = $model->count(); //总数
        $limit = $request->has('limit') ? $request->limit : 20;
        $page = $request->has('page') ? ($request->page - 1) * $limit : 0;
        $data = $model->offset($page)->limit($limit)->get();
       
        $data = $data->map(function($query){
            $query->role = $query->roles->pluck('id');
            return $query;
        });

        return json_encode(['code' => 0, 'data' => $data, 'count' => $total]);
    }

    public function changeStatus(Request $request)
    {
        $res = Admin::where('id', $request->id)->update([
            'status' => $request->status == 1?0:1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $res?$this->responseData(200, '修改成功') : $this->responseData(400, '修改失败');
    }
    
    public function changePassword(Request $request)
    {
        if(empty($request->oldpassword) || empty($request->xinpassword) || empty($request->qrpassword)){
            return $this->responseData(400, '参数错误');
        }
        if($request->xinpassword != $request->qrpassword){
            return $this->responseData(400, '密码与确认密码不一致');
        }
        $admin = auth('admin')->user();

        $oldpassword = md5($request->oldpassword);
        $result = Hash::check($oldpassword,$admin->password);
        if(empty($result)){
            return $this->responseData(400, '原密码错误');
        }
        
        $newpassword = md5($request->xinpassword);
        $xinpassword = Hash::make($newpassword);

        $res = Admin::where('id', $admin->id)->update([
            'password' => $xinpassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $res?$this->responseData(200, '修改成功') : $this->responseData(400, '修改失败');
    }

    public function add(Request $request)
    {
        $password = md5($request->password);
        $res = Admin::create([
            'name' => $request->name,
            'password' => Hash::make($password),
            'status' => $request->status,
            'white_ip' => $request->white_ip ?? ''
        ]);
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
    }

    public function delete(Request $request)
    {
        $res = Admin::where('id', $request->id)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Admin::where('id', $request->id)->first();
            $user->name = $request->name;
            if ( $request->password != '') {
                $password = md5($request->password);
                $user->password = Hash::make($password);
            }
            $user->white_ip = $request->white_ip ?? '';
            $user->save();
            $user->syncRoles($request->role_id);
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
        
    }

    public  function compare($before,$after){
        if (!is_array($before) || !is_array($after)) {
            return false;
        }
        $add = [];
        $del = [];

        foreach ($after as $val) {
            if (!in_array($val, $before)) {
                array_push($add, $val);
            }
        }

        foreach ($before as $val) {
            if (!in_array($val, $after)) {
                array_push($del, $val);
            }
        }

        return [ 'add' => $add, 'del' => $del];
    }

}
