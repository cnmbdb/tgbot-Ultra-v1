<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyThirdPart extends Model
{
	

    protected $table = 't_energy_third_part';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
