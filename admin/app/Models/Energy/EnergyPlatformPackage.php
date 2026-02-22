<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyPlatformPackage extends Model
{
	protected $connection = 'mysql';

    protected $table = 'energy_platform_package';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
