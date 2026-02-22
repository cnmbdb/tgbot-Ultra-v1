<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class MonitorBot extends Model
{
	

    protected $table = 't_monitor_bot';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
