<?php

namespace App\Models\Transit;

use Illuminate\Database\Eloquent\Model;

class TransitWalletTradeList extends Model
{
	
	
    protected $table = 't_transit_wallet_trade_list';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
