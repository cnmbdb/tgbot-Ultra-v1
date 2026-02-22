<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Model;

class TelegramBotCommand extends Model
{
    protected $table = 't_telegram_bot_command';

    protected $primaryKey  = 'rid';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];
}
