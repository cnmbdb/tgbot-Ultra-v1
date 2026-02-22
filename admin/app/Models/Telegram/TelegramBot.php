<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class TelegramBot extends Model
{
    protected $connection = 'pgsql'; // 使用 PostgreSQL 连接

    protected $table = 't_telegram_bot';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
