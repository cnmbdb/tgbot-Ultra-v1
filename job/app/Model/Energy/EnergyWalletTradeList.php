<?php

declare (strict_types=1);
namespace App\Model\Energy;
use Hyperf\DbConnection\Model\Model;

/**
 */
class EnergyWalletTradeList extends Model
{


    /**
     * The table associated with the model.
     *
     * @var string
     */
    // 使用实际表名 t_energy_wallet_trade_list（PostgreSQL / MySQL 均以 t_ 前缀存储）
    protected $table = 't_energy_wallet_trade_list';

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