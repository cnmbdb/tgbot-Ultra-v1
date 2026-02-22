<?php

namespace App\Http\Controllers\Api\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Energy\EnergyPlatformOrder;
use App\Models\Energy\EnergyPlatform;
use App\Models\Energy\EnergyPlatformBot;
use Telegram\Bot\Api;

class TestController extends Controller
{
    //机器人通知
    public function getdata(Request $request)
    {
    }
}
