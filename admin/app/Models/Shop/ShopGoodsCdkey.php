<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class ShopGoodsCdkey extends Model
{
	

    protected $table = 't_shop_goods_cdkey';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
