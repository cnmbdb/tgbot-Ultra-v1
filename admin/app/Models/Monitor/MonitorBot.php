<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

class MonitorBot extends Model
{
	protected $connection = 'mysql';

    protected $table = 'monitor_bot';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
