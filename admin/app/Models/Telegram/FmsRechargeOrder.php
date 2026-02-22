<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class FmsRechargeOrder extends Model
{
	protected $connection = 'mysql';

    protected $table = 'fms_recharge_order';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
