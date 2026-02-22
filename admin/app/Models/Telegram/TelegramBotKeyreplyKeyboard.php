<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class TelegramBotKeyreplyKeyboard extends Model
{
	

    protected $table = 't_telegram_bot_keyreply_keyboard';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
