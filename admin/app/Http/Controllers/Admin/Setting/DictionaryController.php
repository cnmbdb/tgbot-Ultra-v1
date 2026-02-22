<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    /**
     * 字典列表
     */
    public function index(Request $request)
    {
        // 占位实现，避免路由错误
        return response()->json([
            'code' => 200,
            'msg' => 'DictionaryController 功能待实现',
            'data' => []
        ]);
    }

    /**
     * 存储字典
     */
    public function store(Request $request)
    {
        // 占位实现，避免路由错误
        return response()->json([
            'code' => 200,
            'msg' => 'DictionaryController 功能待实现',
            'data' => []
        ]);
    }

    /**
     * 更新字典
     */
    public function update(Request $request)
    {
        // 占位实现，避免路由错误
        return response()->json([
            'code' => 200,
            'msg' => 'DictionaryController 功能待实现',
            'data' => []
        ]);
    }

    /**
     * 删除字典
     */
    public function delete(Request $request)
    {
        // 占位实现，避免路由错误
        return response()->json([
            'code' => 200,
            'msg' => 'DictionaryController 功能待实现',
            'data' => []
        ]);
    }
}
