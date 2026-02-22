<?php

namespace App\Models\Energy;

use Illuminate\Database\Eloquent\Model;

class EnergyAiTrusteeship extends Model
{
	

    protected $table = 't_energy_ai_trusteeship';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
