<?php

namespace App\Models\Transit;

use Illuminate\Database\Eloquent\Model;

class TransitWalletBlack extends Model
{
	protected $connection = 'mysql';

    protected $table = 'transit_wallet_black';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
