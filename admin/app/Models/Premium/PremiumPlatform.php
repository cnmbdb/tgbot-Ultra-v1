<?php

namespace App\Models\Premium;

use Illuminate\Database\Eloquent\Model;

class PremiumPlatform extends Model
{
	protected $connection = 'mysql';

    protected $table = 'premium_platform';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
