<?php

declare (strict_types=1);
namespace App\Model\Collection;
use Hyperf\DbConnection\Model\Model;

/**
 */
class CollectionWalletList extends Model
{


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collection_wallet_list';

    public $primaryKey  = 'rid';

    public $keyType  = 'int';

    public $timestamps  = false;

    public $incrementing  = true;



    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

}