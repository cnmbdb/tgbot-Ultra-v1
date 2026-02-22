<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyAiBishu extends Model
{
	protected $connection = 'mysql';

    protected $table = 'energy_ai_bishu';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
