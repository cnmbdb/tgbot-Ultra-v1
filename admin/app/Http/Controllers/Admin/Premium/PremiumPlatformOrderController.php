<?php

namespace App\Http\Controllers\Admin\Premium;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Telegram\TelegramBot;
use App\Models\Premium\PremiumPlatform;
use App\Models\Premium\PremiumPlatformOrder;

class PremiumPlatformOrderController extends Controller
{
    public $SourceType = ['1' => '人工下单','2' => '自动下单'];
    public $Status = ['0' => '待支付','1' => '待开通','2' => '已开通','3' => '已过期','4' => '会员取消','5' => '开通失败','6' => '开通中'];
    
    public function index(Request $request)
    {
        $PlatformName = PremiumPlatform::pluck('rid','rid'); 
        $SourceType = $this->SourceType;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.premium.order.index',compact("PlatformName","SourceType","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = PremiumPlatformOrder::from('t_premium_platform_order as a')
                ->leftjoin('t_telegram_bot as b','a.bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->premium_tg_username != '') {
                    $query->where('a.premium_tg_username', 'like' ,"%" . $request->premium_tg_username ."%");
                }
                if ($request->bot_rid != '') {
                    $query->where('a.bot_rid', $request->bot_rid);
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $SourceType = $this->SourceType;
        $Status = $this->Status;
        
        $data = $data->map(function($query) use ($Status,$SourceType){
            $query->source_type_val = $SourceType[$query->source_type];
            $query->status_val = $Status[$query->status];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
}
