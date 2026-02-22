<?php

namespace App\Models\Collection;

use Illuminate\Database\Eloquent\Model;

class CollectionWalletList extends Model
{
    protected $table = 't_collection_wallet_list';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
