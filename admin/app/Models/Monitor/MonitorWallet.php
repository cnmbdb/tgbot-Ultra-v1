<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class MonitorWallet extends Model
{
	

    protected $table = 't_monitor_wallet';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
