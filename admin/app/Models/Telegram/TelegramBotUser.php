<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class TelegramBotUser extends Model
{
	protected $connection = 'mysql';

    protected $table = 'telegram_bot_user';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
