<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class FmsRechargeOrder extends Model
{
	

    protected $table = 't_fms_recharge_order';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
