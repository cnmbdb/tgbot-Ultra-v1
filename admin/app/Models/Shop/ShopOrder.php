<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class ShopOrder extends Model
{
	

    protected $table = 't_shop_order';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
