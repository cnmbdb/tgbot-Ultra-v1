<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyPlatform extends Model
{
	protected $connection = 'mysql';

    protected $table = 'energy_platform';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
