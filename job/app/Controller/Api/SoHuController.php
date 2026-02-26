<?php
declare(strict_types=1);

namespace App\Controller\Api;
use App\Controller\AbstractController;
use App\Model\Energy\EnergyAiBishu;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Library\Log;

class SoHuController extends AbstractController
{
    // 搜狐笔数回调通知
    public function notice(RequestInterface $request)
    {
        //仅笔数才通知
        if($request->input('type') != 'times'){
            return $this->responseApi(200,'success');
        }
        
        //仅代理成功通知 init=未处理,waiting_delegate=正在发送,delegate_success=代理成功,delegate_failed=代理失败,waiting_un_delegate=等待回收,un_delegate_success=回收成功,un_delegatefailed=回收失败
        if($request->input('status') != 'delegate_success'){
            return $this->responseApi(200,'success');
        }
        
    	$receiveAddress = $request->input('address');
    	$residue = $request->input('remain_times');
    	
    	if(!empty($receiveAddress)){
    	    //查地址通知
        	$bishu = EnergyAiBishu::from('t_energy_ai_bishu as a')
                    ->leftJoin('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                    ->leftJoin('t_telegram_bot as c','a.bot_rid','c.rid')
                    ->where('a.wallet_addr',$receiveAddress)
                    ->select('a.rid','a.tg_uid','a.wallet_addr','c.bot_token','a.is_notice_admin','a.is_notice','b.tg_admin_uid','b.tg_notice_obj_send','c.bot_username','c.bot_admin_username','b.per_bishu_energy_quantity','a.bot_rid','a.max_buy_quantity','a.total_buy_quantity')
                    ->first();
            
            //内联按钮
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '笔数套餐', 'url' => 'https://t.me/'.$bishu->bot_username]
                    ],
                    [
                        ['text' => '联系客服', 'url' => 'https://t.me/'.mb_substr($bishu->bot_admin_username,1)],
                        ['text' => 'TRX闪兑', 'url' => 'https://t.me/'.$bishu->bot_username]
                    ]
                ]
            ];
            
            $encodedKeyboard = json_encode($keyboard);
            
        	if(!empty($bishu) && isset($bishu->tg_uid) && !empty($bishu->tg_uid)){
        	    $replytextuid = "🖌<b>新的笔数能量订单成功</b> \n"
                                ."➖➖➖➖➖➖➖➖\n"
                                ."<b>下单模式</b>：笔数套餐\n"
                                ."<b>能量数量</b>：".$bishu->per_bishu_energy_quantity." \n"
                                ."<b>能量地址</b>：<code>". $receiveAddress ."</code>\n\n"
                                ."<b>能量已经到账！请在时间范围内使用！</b>\n"
                                ."发送 /buyenergy 继续购买能量！\n\n"
                                ."⚠️<u>预计剩余：</u>".($residue + ($bishu->max_buy_quantity - $bishu->total_buy_quantity))." 次\n"
                                ."➖➖➖➖➖➖➖➖";
    
                $sendlistuid = explode(',',$bishu->tg_uid);
            
                foreach ($sendlistuid as $x => $y) {
                    $sendmessageurl = 'https://api.telegram.org/bot'.$bishu->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytextuid).'&parse_mode=HTML&reply_markup='.urlencode($encodedKeyboard);
                    Get_Pay($sendmessageurl);
                }
    	    }
    	    
    	    //通知到群
            if(!empty($bishu->tg_notice_obj_send) && $bishu->tg_notice_obj_send != ''){
                if($bishu->bot_rid == 12 && $residue <= 10){
                    $replytext = "🖌<b>新的笔数能量订单成功</b> \n"
                        ."➖➖➖➖➖➖➖➖\n"
                        ."<b>下单模式</b>：笔数套餐\n"
                        ."<b>能量数量</b>：".$bishu->per_bishu_energy_quantity." \n"
                        ."<b>能量地址</b>：<code>". $receiveAddress ."</code>\n\n"
                        ."<b>能量已经到账！请在时间范围内使用！</b>\n"
                        ."发送 /buyenergy 继续购买能量！\n\n"
                        ."⚠️<u>预计剩余：</u>".($residue + ($bishu->max_buy_quantity - $bishu->total_buy_quantity))." 次\n"
                        ."➖➖➖➖➖➖➖➖";
                        
                    $sendlist = explode(',',$bishu->tg_notice_obj_send);
                
                    foreach ($sendlist as $x => $y) {
                        $sendmessageurl = 'https://api.telegram.org/bot'.$bishu->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML&reply_markup='.urlencode($encodedKeyboard);
                        Get_Pay($sendmessageurl);
                    }
                }elseif($bishu->bot_rid != 12){
                    $replytext = "🖌<b>新的笔数能量订单成功</b> \n"
                        ."➖➖➖➖➖➖➖➖\n"
                        ."<b>下单模式</b>：笔数套餐\n"
                        ."<b>能量数量</b>：".$bishu->per_bishu_energy_quantity." \n"
                        ."<b>能量地址</b>：<code>". $receiveAddress ."</code>\n\n"
                        ."<b>能量已经到账！请在时间范围内使用！</b>\n"
                        ."发送 /buyenergy 继续购买能量！\n\n"
                        ."⚠️<u>预计剩余：</u>".($residue + ($bishu->max_buy_quantity - $bishu->total_buy_quantity))." 次\n"
                        ."➖➖➖➖➖➖➖➖";
                        
                    $sendlist = explode(',',$bishu->tg_notice_obj_send);
                
                    foreach ($sendlist as $x => $y) {
                        $sendmessageurl = 'https://api.telegram.org/bot'.$bishu->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytext).'&parse_mode=HTML&reply_markup='.urlencode($encodedKeyboard);
                        Get_Pay($sendmessageurl);
                    }
                }
            }
    	}
    	
    	return $this->responseApi(200,'success');
    }
    
    /**
     * 记入日志
     * @param $log_title [日志路径]
     * @param $message [内容，不支持数组]
     * @param $remarks [备注]
    */
    protected function log($log_title,$message,$remarks='info'){
        Log::get($remarks,$log_title)->info($message);
    }
}
