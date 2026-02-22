<?php

namespace App\Models\Transit;

use Illuminate\Database\Eloquent\Model;

class TransitUserWallet extends Model
{
	protected $connection = 'mysql';

    protected $table = 'transit_user_wallet';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
