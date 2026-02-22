<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyWalletTradeList extends Model
{
	protected $connection = 'mysql';

    protected $table = 'energy_wallet_trade_list';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
