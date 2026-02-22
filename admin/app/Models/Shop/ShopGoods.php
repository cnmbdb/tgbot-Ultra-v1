<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class ShopGoods extends Model
{
	protected $connection = 'mysql';

    protected $table = 'shop_goods';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
