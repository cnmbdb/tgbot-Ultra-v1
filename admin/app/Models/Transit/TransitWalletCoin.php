<?php

namespace App\Models\Transit;

use Illuminate\Database\Eloquent\Model;

class TransitWalletCoin extends Model
{
	
	
    protected $table = 't_transit_wallet_coin';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
