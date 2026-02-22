<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyThirdPart extends Model
{
	protected $connection = 'mysql';

    protected $table = 'energy_third_part';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
