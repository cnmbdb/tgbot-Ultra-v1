<?php
namespace App\Task;

use App\Model\Energy\EnergyPlatformBot;
use App\Model\Energy\EnergyAiBishu;
use App\Model\Energy\EnergyWalletTradeList;
use App\Library\Log;
use App\Service\Bus\TronServices;

class MonitorEnergyWalletTradeLost
{
    public function execute()
    { 
        try {
            $lostblock = json_decode(getRedis('lostblockenergy'),true) ?? [];
            
            if(!empty($lostblock)){
                $data = EnergyPlatformBot::select('receive_wallet')->where('status',0)->whereRaw('length(receive_wallet) = 34')->get()->toArray();
                
                $data2 = EnergyAiBishu::from('t_energy_ai_bishu as a')
                    ->Join('t_telegram_bot as b','a.bot_rid','b.rid')
                    ->Join('energy_platform_bot as c','a.bot_rid','c.bot_rid')
                    ->where('a.status',0)
                    ->where('c.is_open_bishu','Y')
                    ->whereRaw('length(a.wallet_addr) = 34 and a.max_buy_quantity > a.total_buy_quantity')
                    ->select('a.*')
                    ->get()->toArray();
                
                if((!empty($data) || !empty($data2)) && !empty($lostblock)){
                    $api_key = config('apikey.gridapikey');
                    $apikeyrand = $api_key[array_rand($api_key)];
                    
                    // $node_key = config('apikey.quicknode');
                    // $nodekeyrand = $node_key[array_rand($node_key)];
                    
                    //波场接口API
                    $TronApiConfig = [
                        'url' => "https://api.trongrid.io",  //$nodekeyrand
                        'api_key' => $apikeyrand,
                    ]; 
                    
                    $tron = new TronServices($TronApiConfig,'1111111','222222');
                    $tronres = $tron->getBlock(current($lostblock));
                    
                    if(!empty($tronres['transactions'])){
                        $currentblock = $tronres['block_header']['raw_data']['number'];
                        $blocktimestamp = $tronres['block_header']['raw_data']['timestamp'];
                        
                        array_shift($lostblock);
                        setRedis('lostblockenergy',json_encode($lostblock));
                        
                        //区块的交易详细
                        foreach ($tronres['transactions'] as $x => $y) {
                            //如果是合约事件
                            if($y['raw_data']['contract'][0]['type'] == 'TriggerSmartContract' && $y['ret'][0]['contractRet'] == 'SUCCESS'){
                                $dataaa = $y['raw_data']['contract'][0]['parameter']['value']['data'];
                                $contract_address = $y['raw_data']['contract'][0]['parameter']['value']['contract_address']; //USDT:41a614f803b6fd780986a42c78ec9c7f77e6ded13c
                                
                                //取合约的transfer方法
                                if(in_array(mb_substr($dataaa,0,8),['a9059cbb']) && $contract_address == '41a614f803b6fd780986a42c78ec9c7f77e6ded13c'){
                                    $toaddress = $tron->addressFromHex('41' . mb_substr($dataaa,32,40));
                                    $fromaddress = $tron->addressFromHex($y['raw_data']['contract'][0]['parameter']['value']['owner_address']);
                                    $amount = $tron->dataAmountFormat(mb_substr($dataaa,72,64));
                                    
                                    //转入地址是否能量购买的地址
                                    $isto = array_search($toaddress,array_column($data,'receive_wallet'));
                                    //如果是转入
                                    if(($isto !== false && $amount >= 1 && mb_substr($dataaa,0,8) == 'a9059cbb')){
                                        //记录交易
                                        $this->AddWalletData($y['txID'],$fromaddress,$blocktimestamp,$toaddress,'usdt',$amount);
                                    }
                                    
                                    //转出地址是否是用户的笔数地址
                                    $isfrom = array_search($fromaddress,array_column($data2,'wallet_addr'));
                                    //如果是转出
                                    if(($isfrom !== false && $amount > 0 && mb_substr($dataaa,0,8) == 'a9059cbb') || ($isfrom !== false && mb_substr($dataaa,0,8) != 'a9059cbb')){
                                        $found_obj = $data2[$isfrom];
                                        
                                        EnergyAiBishu::where('is_buy','N')->where('rid',$found_obj['rid'])->update(['is_buy' => 'Y']);
                                    }
                                }
                            
                            // trx交易
                            }elseif($y['raw_data']['contract'][0]['type'] == 'TransferContract' && $y['ret'][0]['contractRet'] == 'SUCCESS'){
                                $toaddress = $tron->addressFromHex($y['raw_data']['contract'][0]['parameter']['value']['to_address']);
                                $fromaddress = $tron->addressFromHex($y['raw_data']['contract'][0]['parameter']['value']['owner_address']);
                                $amount = calculationExcept($y['raw_data']['contract'][0]['parameter']['value']['amount'],6);
                                
                                //转入地址是否能量购买的地址
                                $isto = array_search($toaddress,array_column($data,'receive_wallet'));
                                //如果是转入
                                if($isto !== false && $amount >= 1){
                                    //记录交易
                                    $this->AddWalletData($y['txID'],$fromaddress,$blocktimestamp,$toaddress,'trx',$amount);
                                }
                                
                            }
                        }
                    }
                }
            }
            
        }catch (\Exception $e){
            $this->log('getenergywallettrxtrade','----------Lost任务执行报错，请联系管理员。报错原因：----------'.$e->getMessage());
        }
    }
    
    /**
     * 整合添加收款数据
    */
    public function AddWalletData($txID,$fromaddress,$blocktimestamp,$toaddress,$coin,$amount){
        try {
            $txid_list = [];
        
            $txid_list['tx_hash'] = $txID;       //交易hash 
            $txid_list['transferfrom_address'] = $fromaddress;       //来源钱包地址  
            $txid_list['timestamp'] = $blocktimestamp;        //时间戳  
            
            $txid_list['transferto_address'] = $toaddress;        //收款钱包地址  
    
            $txid_list['coin_name'] = $coin;
            $txid_list['amount'] = $amount;     //交易数额 
            $txid_list['get_time'] = nowDate();       //拉取时间 
    
            $txid_list['process_status'] = 1;      //待兑换
            $txid_list['process_comments'] = '待处理';      //处理备注  
            $txid_list['process_time'] = nowDate();        //处理时间
    
            EnergyWalletTradeList::insert($txid_list);       //添加收款钱包交易列表
    
            return ['code' => 200];
            
        } catch (\Exception $e ) {
            $this->log('getenergywallettrxtrade','交易插入失败，已存在：'.$txID);
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