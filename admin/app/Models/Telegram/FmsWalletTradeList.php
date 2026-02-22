<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class FmsWalletTradeList extends Model
{
	protected $connection = 'mysql';

    protected $table = 'fms_wallet_trade_list';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
