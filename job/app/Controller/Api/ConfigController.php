<?php
declare(strict_types=1);

namespace App\Controller\Api;
use App\Controller\AbstractController;
use App\Library\Redis;

class ConfigController extends AbstractController
{
    // 清除定时任务缓存
    public function clearTiming()
    {
    	try {
            $redis = Redis::getInstance();
            $data = $redis->keys('framework/crontab*');
            if(!empty($data)){
                $redis->del(...$data);
            }
            return $this->responseApi(200,'success');
        } catch (\Exception $e) {
            return $this->responseApi(200,'success'); // 即使出错也返回成功，避免阻塞
        }
    }
    
    // 检查是否启动
    public function checkStatus()
    {
        return $this->responseApi(200,'success');
    	
    }
}
