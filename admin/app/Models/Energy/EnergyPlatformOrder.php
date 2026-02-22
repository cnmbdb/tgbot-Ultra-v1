<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyPlatformOrder extends Model
{
	protected $connection = 'mysql';

    protected $table = 'energy_platform_order';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
