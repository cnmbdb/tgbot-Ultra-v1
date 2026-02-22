<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyQuickOrder extends Model
{
	

    protected $table = 't_energy_quick_order';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
