<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class MonitorWallet extends Model
{
	protected $connection = 'mysql';

    protected $table = 'monitor_wallet';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
