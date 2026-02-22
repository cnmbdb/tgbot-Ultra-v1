<?php

namespace App\Models\Collection;

use Illuminate\Database\Eloquent\Model;

class CollectionWallet extends Model
{
    protected $table = 't_collection_wallet';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
