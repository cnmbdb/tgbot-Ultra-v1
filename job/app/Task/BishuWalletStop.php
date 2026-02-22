<?php
namespace App\Task;

use App\Model\Energy\EnergyAiBishu;
use App\Model\Energy\EnergyPlatformOrder;
use App\Library\Log;

class BishuWalletStop
{
    public function execute()
    { 
        //笔数套餐超过滞留天数自动停止
        try {
            $data = EnergyAiBishu::from('energy_ai_bishu as a')
                    ->Join('telegram_bot as b','a.bot_rid','b.rid')
                    ->where('a.status',0)
                    ->where('a.bishu_stop_day','>',0)
                    ->whereRaw('length(t_a.wallet_addr) = 34 and t_a.max_buy_quantity > t_a.total_buy_quantity')
                    ->select('a.*','b.bot_token')
                    ->get();
            
            $this->log('bishuwalletstop','开始执行，有 '.$data->count().' 个地址需要检测');
            
            if($data->count() > 0){
                foreach ($data as $k => $v) {
                    //查询地址最近的几笔是否都是过期代理的，第一条是正在代理中的没有回收时间，从第二条开始查
                    $energyOrder = EnergyPlatformOrder::where('receive_address',$v->wallet_addr)->orderBy('rid','desc')->offset(1)->limit($v->bishu_stop_day)->get();
                    
                    if($energyOrder->count() == $v->bishu_stop_day){
                        $stopDay = 0;
                        foreach ($energyOrder as $ka => $va) {
                            
                            if(!empty($va->recovery_time) && !empty($va->energy_time)){
                                $ts1 = strtotime($va->recovery_time);
                                $ts2 = strtotime($va->energy_time);
                                
                                if ($ts1 !== false && $ts2 !== false && abs($ts1 - $ts2) >= 86400) {
                                    $stopDay++;
                                }
                            }
                        }
                        
                        if($stopDay == $v->bishu_stop_day){
                            $this->log('bishuwalletstop',$v->wallet_addr.' 地址超过 '.$v->bishu_stop_day.' 天未使用能量，暂停笔数');
                            
                            $v->status = 1;
                            $v->back_comments = '滞留'.$v->bishu_stop_day.'天，自动暂停：'.nowDate();
                            $v->save();
                            
                            if(!empty($v->tg_uid) && $v->tg_uid != '' ){
                                $replytextuid = "您的笔数地址 <code>".$v->wallet_addr."</code> 超过 ".$v->bishu_stop_day." 天滞留未使用能量，自动暂停笔数！";
                                $sendlistuid = explode(',',$v->tg_uid);
            
                                foreach ($sendlistuid as $x => $y) {
                                    $sendmessageurl = 'https://api.telegram.org/bot'.$v->bot_token.'/sendMessage?chat_id='.$y.'&text='.urlencode($replytextuid).'&parse_mode=HTML';
                                    Get_Pay($sendmessageurl);
                                }
                            }
                        }
                    }
                }
            }
            
        }catch (\Exception $e){
            $this->log('bishuwalletstop','----------任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
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