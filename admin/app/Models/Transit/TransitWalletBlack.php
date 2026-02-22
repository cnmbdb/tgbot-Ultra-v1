<?php

namespace App\Models\Transit;

use Illuminate\Database\Eloquent\Model;

class TransitWalletBlack extends Model
{
	
	
    protected $table = 't_transit_wallet_black';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
