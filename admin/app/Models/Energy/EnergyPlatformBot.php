<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyPlatformBot extends Model
{
	

    protected $table = 't_energy_platform_bot';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
