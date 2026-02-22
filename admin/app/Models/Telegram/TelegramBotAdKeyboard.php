<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class TelegramBotAdKeyboard extends Model
{
	protected $connection = 'mysql';

    protected $table = 'telegram_bot_ad_keyboard';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
