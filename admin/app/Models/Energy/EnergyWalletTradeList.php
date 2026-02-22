<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyWalletTradeList extends Model
{
	
	
    protected $table = 't_energy_wallet_trade_list';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
