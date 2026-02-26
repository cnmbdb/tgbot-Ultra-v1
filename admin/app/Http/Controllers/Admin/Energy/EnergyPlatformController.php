<?php

namespace App\Http\Controllers\Admin\Energy;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Energy\EnergyPlatform;
use App\Models\Telegram\TelegramBot;
use App\Http\Services\RsaServices;

class EnergyPlatformController extends Controller
{
    public $PlatformName = ['1' => 'Neee.cc','2' => 'RentEnergysBot','3' => '自己质押代理','4' => 'trongas.io(平台已关闭)','5' => '机器人开发代理(平台已关闭)','6' => 'Sohu搜狐','7' => 'NL-API'];
    public $Status = ['开启','关闭'];
    public $PollGroup = ['A' => 'A组','B' => 'B组','C' => 'C组','D' => 'D组','E' => 'E组','F' => 'F组','G' => 'G组'];
    
    public function index(Request $request)
    {
        $PlatformName = $this->PlatformName;
        $Status = $this->Status;
        $PollGroup = $this->PollGroup;
        $botData = TelegramBot::pluck('bot_username','rid'); 
        
        return view('admin.energy.platform.index',compact("PlatformName","Status","PollGroup","botData"));
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = EnergyPlatform::from('t_energy_platform as a')
                ->join('t_telegram_bot as b','a.tg_notice_bot_rid','b.rid')
                ->where(function($query) use ($request){
                if ($request->platform_uid != '') {
                    $query->where('a.platform_uid', 'like' ,"%" . $request->platform_uid ."%");
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*','b.bot_token','b.bot_firstname','b.bot_username')->orderBy('a.rid','desc')->get();
        
        $PlatformName = $this->PlatformName;
        $PollGroup = $this->PollGroup;
        $rsa_services = new RsaServices();
        
        $data = $data->map(function($query) use ($rsa_services,$PlatformName,$PollGroup){
            $platform_apikey = $rsa_services->privateDecrypt($query->platform_apikey);        //解密
            $query->platform_apikey = mb_substr($platform_apikey, 0,4).'****'.mb_substr($platform_apikey, -4,4);
            $query->platform_name_val = $PlatformName[$query->platform_name];
            $query->poll_group_val = $PollGroup[$query->poll_group];
            return $query;
        });

        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
    
    //添加
    public function add(Request $request)
    {
        DB::beginTransaction();
        try {
            // 处理 poll_group（character 类型，固定长度）
            $poll_group = !empty($request->poll_group) ? substr(trim($request->poll_group), 0, 1) : 'A';
            
            // 处理 platform_name（smallint 类型）
            $platform_name = !empty($request->platform_name) ? intval($request->platform_name) : 1;
            
            // 处理 platform_uid（可以为空）
            $platform_uid = !empty($request->platform_uid) ? trim($request->platform_uid) : null;
            
            // 处理 alert_platform_balance（numeric 类型，不能为空）
            $alert_platform_balance = (isset($request->alert_platform_balance) && $request->alert_platform_balance !== '' && $request->alert_platform_balance !== null) 
                ? floatval($request->alert_platform_balance) 
                : 0;
            
            // 处理 seq_sn（integer 类型，不能为空）
            $seq_sn = (isset($request->seq_sn) && $request->seq_sn !== '' && $request->seq_sn !== null) 
                ? intval($request->seq_sn) 
                : 0;
            
            // 处理 tg_notice_bot_rid（integer 类型，可以为空）
            $tg_notice_bot_rid = (isset($request->tg_notice_bot_rid) && $request->tg_notice_bot_rid !== '' && $request->tg_notice_bot_rid !== null) 
                ? intval($request->tg_notice_bot_rid) 
                : null;
            
            $data = [
                'poll_group' => $poll_group,
                'platform_name' => $platform_name,
                'platform_uid' => $platform_uid,
                'alert_platform_balance' => $alert_platform_balance,
            'tg_notice_obj' => $request->tg_notice_obj ?? '',
                'tg_notice_bot_rid' => $tg_notice_bot_rid,
                'seq_sn' => $seq_sn,
            'comments' => $request->comments ?? '',
            'create_time' => nowDate()
            ];
            
            // 如果提供了 platform_apikey，需要加密保存（NL-API 平台需要）
            if(!empty($request->platform_apikey)){
                try {
                    $rsa_services = new RsaServices();
                    $data['platform_apikey'] = $rsa_services->publicEncrypt($request->platform_apikey);
                    $data['permission_id'] = $request->permission_id ?? 0;
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('RSA加密失败: '.$e->getMessage());
                    return $this->responseData(400, 'API密钥加密失败：'.$e->getMessage());
                }
            }
            
            $res = EnergyPlatform::create($data);
            DB::commit();
        return $res ? $this->responseData(200, '添加成功') : $this->responseData(400, '添加失败');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('添加能量平台失败: '.$e->getMessage().' | 堆栈: '.$e->getTraceAsString());
            return $this->responseData(400, '添加失败：'.$e->getMessage());
        }
    }
    
    //删除
    public function delete(Request $request)
    {
        $res = EnergyPlatform::where('rid', $request->rid)->delete();
        return $res ? $this->responseData(200, '删除成功') : $this->responseData(400, '删除失败');
    }

    //编辑
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = EnergyPlatform::where('rid', $request->rid)->first();
            $data->poll_group = $request->poll_group;
            $data->platform_name = $request->platform_name;
            $data->platform_uid = $request->platform_uid;
            $data->alert_platform_balance = $request->alert_platform_balance ?? 0;
            $data->tg_notice_obj = $request->tg_notice_obj ?? '';
            $data->tg_notice_bot_rid = $request->tg_notice_bot_rid ?? '';
            $data->seq_sn = $request->seq_sn ?? 0;
            $data->comments = $request->comments ?? '';
            $data->update_time = nowDate();
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //编辑apikey
    public function updateapikey(Request $request)
    {
        $rsa_services = new RsaServices();
        $platform_apikey = $rsa_services->publicEncrypt($request->platform_apikey);
            
        DB::beginTransaction();
        try {
            $data = EnergyPlatform::where('rid', $request->rid)->first();
            $model = EnergyPlatform::where('rid', $request->rid)
                    ->secondData($request->platform_apikey,$data->platform_uid);
            $data->platform_apikey = $platform_apikey;
            $data->permission_id = $request->permission_id ?? 0;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }
    
    //编辑状态
    public function change_status(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = EnergyPlatform::where('rid', $request->rid)->first();
            $data->status = $request->status == 1 ? 0 : 1;
            $data->save();
            DB::commit();
            return $this->responseData(200, '更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseData(400, '更新失败'.$e->getMessage());
        }
    }

    /**
     * NL-API 平台余额充值：为指定 API 账号创建能量池充值订单
     *
     * 前端弹窗会传入：
     * - rid: 能量平台 rid（必须是 platform_name = 7 的 NL-API 平台）
     * - amount: 充值 TRX 金额（整数）
     *
     * 本方法会调用 tgnl-home 的 /api/api-recharge-orders 接口创建充值订单，
     * 返回支付地址、应付金额（带两位随机小数）、过期时间等信息，用于生成二维码和倒计时。
     */
    public function nlApiRecharge(Request $request)
    {
        $rid = intval($request->input('rid', 0));
        $amount = intval($request->input('amount', 0));

        if ($rid <= 0 || $amount <= 0) {
            return $this->responseData(400, '参数错误：平台ID或充值金额不合法');
        }

        // 固定收款地址（与 tgnl-home 目前运营中的收款地址保持一致）
        $paymentAddress = 'TJdtCWfm4iaqcQVMJchrobkbP5Y9yqNpPf';

        // 查询 NL-API 能量平台配置
        $platform = EnergyPlatform::where('rid', $rid)->first();
        if (!$platform) {
            return $this->responseData(400, '能量平台不存在');
        }
        if (intval($platform->platform_name) !== 7) {
            return $this->responseData(400, '仅支持 NL-API 平台充值');
        }

        $apiUsername = $platform->platform_uid;
        if (empty($apiUsername)) {
            return $this->responseData(400, 'NL-API 平台未配置 API 用户名');
        }

        // NL-API 基础地址：优先环境变量，其次从备注中解析 nl_api_url=...
        $nlApiBaseUrl = env('NL_API_BASE_URL', 'https://tgnl-home.hfz.pw');
        if (empty($nlApiBaseUrl) && !empty($platform->comments)) {
            if (preg_match('/nl_api_url=([^\s]+)/i', $platform->comments, $matches)) {
                $nlApiBaseUrl = trim($matches[1]);
            }
        }
        if (empty($nlApiBaseUrl)) {
            return $this->responseData(400, 'NL-API 域名未配置');
        }

        // 组装请求参数
        $payload = [
            'apiUsername'       => $apiUsername,
            'paymentAddress'    => $paymentAddress,
            'amountTrx'         => $amount,   // tgnl-home 会自动加上随机两位小数
            'telegramChatId'    => null,
            'telegramMessageId' => null,
        ];

        $url = rtrim($nlApiBaseUrl, '/') . '/api/api-recharge-orders';
        $header = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        try {
            $raw = Get_Curl($url, json_encode($payload), $header);
        } catch (\Throwable $e) {
            return $this->responseData(400, '创建充值订单失败：' . $e->getMessage());
        }

        if (empty($raw)) {
            return $this->responseData(400, '创建充值订单失败：接口返回为空');
        }

        $res = json_decode($raw, true);
        if (!is_array($res) || empty($res['success']) || empty($res['data'])) {
            $msg = isset($res['error']) ? $res['error'] : '能量池接口返回异常';
            return $this->responseData(400, '创建充值订单失败：' . $msg);
        }

        $data = $res['data'];
        $orderId       = $data['orderId']    ?? '';
        $amountTrx     = $data['amountTrx']  ?? 0;
        $expiresAt     = $data['expiresAt']  ?? '';
        $createdAt     = $data['createdAt']  ?? '';
        $payAddress    = $data['paymentAddress'] ?? $paymentAddress;

        if (empty($orderId) || empty($amountTrx) || empty($expiresAt)) {
            return $this->responseData(400, '创建充值订单失败：返回数据不完整');
        }

        // 构造 tron: 协议 URI，方便生成二维码
        $tronUri = sprintf('tron:%s?amount=%s', $payAddress, $amountTrx);

        return $this->responseData(200, 'success', [
            'order_id'        => $orderId,
            'api_username'    => $apiUsername,
            'payment_address' => $payAddress,
            'amount_trx'      => $amountTrx,
            'expires_at'      => $expiresAt,
            'created_at'      => $createdAt,
            'tron_uri'        => $tronUri,
        ]);
    }

    /**
     * 获取 NL-API 充值历史记录
     * 
     * 调用 tgnl-home 的 GET /api/api-recharge-orders 接口，查询指定 API 账号的充值订单历史
     * 返回最近 10 条订单，包含状态（pending=进行中、paid=已成功、expired=已过期、cancelled=已取消）
     */
    public function nlapiRechargeHistory(Request $request)
    {
        $rid = intval($request->input('rid', 0));

        if ($rid <= 0) {
            return $this->responseData(400, '参数错误：平台ID不合法');
        }

        // 查询 NL-API 能量平台配置
        $platform = EnergyPlatform::where('rid', $rid)->first();
        if (!$platform) {
            return $this->responseData(400, '能量平台不存在');
        }
        if (intval($platform->platform_name) !== 7) {
            return $this->responseData(400, '仅支持 NL-API 平台');
        }

        $apiUsername = $platform->platform_uid;
        if (empty($apiUsername)) {
            return $this->responseData(400, 'NL-API 平台未配置 API 用户名');
        }

        // NL-API 基础地址：优先环境变量，其次从备注中解析 nl_api_url=...
        $nlApiBaseUrl = env('NL_API_BASE_URL', 'https://tgnl-home.hfz.pw');
        if (empty($nlApiBaseUrl) && !empty($platform->comments)) {
            if (preg_match('/nl_api_url=([^\s]+)/i', $platform->comments, $matches)) {
                $nlApiBaseUrl = trim($matches[1]);
            }
        }
        if (empty($nlApiBaseUrl)) {
            return $this->responseData(400, 'NL-API 域名未配置');
        }

        // 调用 tgnl-home 查询充值订单历史
        $url = rtrim($nlApiBaseUrl, '/') . '/api/api-recharge-orders?apiUsername=' . urlencode($apiUsername);
        $header = [
            'Accept: application/json',
        ];

        try {
            $raw = Get_Curl($url, null, $header); // 使用 GET 请求（不传 data 参数）
        } catch (\Throwable $e) {
            return $this->responseData(400, '查询充值历史失败：' . $e->getMessage());
        }

        if (empty($raw)) {
            return $this->responseData(400, '查询充值历史失败：接口返回为空');
        }

        $res = json_decode($raw, true);
        if (!is_array($res) || empty($res['success']) || !isset($res['data'])) {
            $msg = isset($res['error']) ? $res['error'] : '能量池接口返回异常';
            return $this->responseData(400, '查询充值历史失败：' . $msg);
        }

        $orders = $res['data'];
        if (!is_array($orders)) {
            $orders = [];
        }

        // 只返回最近 10 条，并按时间倒序
        $orders = array_slice($orders, 0, 10);

        // 格式化订单数据，映射状态
        $formattedOrders = [];
        foreach ($orders as $order) {
            $status = $order['status'] ?? 'pending';
            $statusText = '未知';
            $statusColor = '#999';
            
            if ($status === 'pending') {
                $statusText = '进行中';
                $statusColor = '#FFB800'; // 黄色
            } elseif ($status === 'paid') {
                $statusText = '已成功';
                $statusColor = '#5FB878'; // 绿色
            } elseif ($status === 'expired' || $status === 'cancelled') {
                $statusText = $status === 'expired' ? '已过期' : '已取消';
                $statusColor = '#999'; // 灰色
            }

            $formattedOrders[] = [
                'order_id' => $order['id'] ?? '',
                'amount_trx' => $order['amount_trx'] ?? 0,
                'status' => $status,
                'status_text' => $statusText,
                'status_color' => $statusColor,
                'created_at' => $order['created_at'] ?? '',
                'expires_at' => $order['expires_at'] ?? '',
                'paid_at' => $order['paid_at'] ?? null,
            ];
        }

        return $this->responseData(200, 'success', [
            'api_username' => $apiUsername,
            'orders' => $formattedOrders,
        ]);
    }
}
