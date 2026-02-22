<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class ShopGoods extends Model
{
	

    protected $table = 't_shop_goods';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
