<?php

namespace App\Models\Premium;

use Illuminate\Database\Eloquent\Model;

class PremiumPlatformPackage extends Model
{
	protected $connection = 'mysql';

    protected $table = 'premium_platform_package';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
