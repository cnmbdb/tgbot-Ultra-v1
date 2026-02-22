<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class SysConfig extends Model
{
    protected $table = 't_sys_config';

    protected $primaryKey  = 'rid';

    public $timestamps = false;

    protected $guarded = [];

    public function getConfigValAttribute($val)
    {
        if (empty($val)) {
            return (object)['url' => ''];
        }
        // 处理转义的 JSON 字符串
        $cleaned = stripslashes($val);
        $decoded = json_decode($cleaned, false);
        if (json_last_error() !== JSON_ERROR_NONE || $decoded === null) {
            // 如果解析失败，尝试直接解析原始值
            $decoded = json_decode($val, false);
            if (json_last_error() !== JSON_ERROR_NONE || $decoded === null) {
                return (object)['url' => ''];
            }
        }
        return $decoded;
    }
}
