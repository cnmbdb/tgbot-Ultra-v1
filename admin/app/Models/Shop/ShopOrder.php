<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShopOrder extends Model
{
    

    protected $table = 't_shop_order';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * 插入数据并返回主键 (修复 PostgreSQL insertGetId 使用 "returning id" 的问题)
     */
    public static function insertGetId($attributes, $sequence = null)
    {
        $instance = new static();
        $instance->fill($attributes);
        
        // 使用原生 SQL 来正确处理 PostgreSQL 的 returning
        $columns = implode(', ', array_keys($attributes));
        $values = implode(', ', array_map(function ($value) use ($instance) {
            return $instance->getConnection()->getPdo()->quote($value);
        }, $attributes));
        
        $sequence = $sequence ?? 't_shop_order_rid_seq';
        $result = DB::select("INSERT INTO {$instance->getTable()} ({$columns}) VALUES ({$values}) RETURNING {$instance->getKeyName()}");
        
        return $result[0]->rid ?? $result[0]->id ?? null;
    }
}
