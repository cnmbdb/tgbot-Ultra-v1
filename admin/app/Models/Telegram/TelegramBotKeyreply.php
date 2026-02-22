<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class TelegramBotKeyreply extends Model
{
	protected $connection = 'mysql';

    protected $table = 'telegram_bot_keyreply';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
