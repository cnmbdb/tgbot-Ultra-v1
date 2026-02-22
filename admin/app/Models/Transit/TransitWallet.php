<?php

namespace App\Models\Transit;

use Illuminate\Database\Eloquent\Model;

class TransitWallet extends Model
{
	
	
    protected $table = 't_transit_wallet';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
