<?php

namespace App\Models\Premium;

use Illuminate\Database\Eloquent\Model;

class PremiumWalletTradeList extends Model
{
	protected $connection = 'mysql';

    protected $table = 'premium_wallet_trade_list';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
