<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class FmsWalletTradeList extends Model
{
	

    protected $table = 't_fms_wallet_trade_list';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
