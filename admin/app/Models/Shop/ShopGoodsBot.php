<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class ShopGoodsBot extends Model
{
	

    protected $table = 't_shop_goods_bot';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
