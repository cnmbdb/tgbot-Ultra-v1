<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyQuickOrder extends Model
{
	protected $connection = 'mysql';

    protected $table = 'energy_quick_order';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
