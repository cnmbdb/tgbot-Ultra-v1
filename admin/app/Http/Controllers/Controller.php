<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function responseData($code, $msg, $data = '')
    {
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function writeRequestLog($request, $channel = '')
    {
        llog('请求URL：' . $request->url(), $channel);
        llog('请求Header：' . json_encode($request->header()), $channel);
        llog('请求Body：' . json_encode($request->all()), $channel);
    }
}
