<?php

namespace App\Http\Controllers\Api\Telegram;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Transit\TransitUserWallet;
use App\Models\Transit\TransitWalletCoin;
use App\Models\Transit\TransitWalletBlack;
use App\Models\Transit\TransitWallet;
use App\Models\Transit\TransitWalletTradeList;
use App\Models\Telegram\TelegramBot;
use App\Models\Telegram\TelegramBotKeyreply;
use App\Models\Telegram\TelegramBotKeyboard;
use App\Models\Telegram\TelegramBotKeyreplyKeyboard;
use App\Models\Telegram\TelegramBotUser;
use App\Models\Telegram\TelegramBotCommand;
use App\Models\Telegram\FmsRechargeOrder;
use App\Models\Energy\EnergyPlatformPackage;
use App\Models\Energy\EnergyPlatform;
use App\Models\Energy\EnergyPlatformBot;
use App\Models\Energy\EnergyPlatformOrder;
use App\Models\Energy\EnergyAiTrusteeship;
use App\Models\Energy\EnergyAiBishu;
use App\Models\Energy\EnergyQuickOrder;
use App\Models\Premium\PremiumPlatformPackage;
use App\Models\Premium\PremiumPlatform;
use App\Models\Premium\PremiumPlatformOrder;
use App\Models\Monitor\MonitorWallet;
use App\Models\Monitor\MonitorBot;
use App\Models\Shop\ShopGoods;
use App\Models\Shop\ShopGoodsBot;
use App\Models\Shop\ShopGoodsCdkey;
use App\Models\Shop\ShopOrder;
use App\Http\Services\RsaServices;
use App\Http\Services\YuZhiServices;
use App\Http\Services\XiaFaServices;
use App\Http\Services\EnergyServices;
use App\Http\Services\Telegram\TelegramBotUserServices;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\FileUpload\InputFile;

class TelegramController extends Controller
{
    // 过滤掉emoji表情
    public function filter_Emoji($str)
    {
        $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
                '/./u',
                function (array $match) {
                    return strlen($match[0]) >= 4 ? '' : $match[0];
                },
                $str);
    
        return $str;
    }
    
    /**
     * 用于关键词匹配：移除所有 emoji 和符号，只保留中文/字母/数字
     * 解决 "❇️智能托管"、"🔠购买靓号" 等带 emoji 的关键词无法匹配数据库 "智能托管"、"购买靓号" 的问题
     */
    public function filterTextForKeywordMatch($str)
    {
        if (empty($str)) {
            return $str;
        }
        // 1. 移除零宽字符（可能影响匹配）
        $str = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{2060}]/u', '', $str);
        // 2. 先移除 4 字节及以上的字符（大部分 emoji，如 🔠）
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);
        // 3. 移除常见符号和 emoji（3 字节）：变体选择符、杂项符号(含❇)、装饰符号等
        $str = preg_replace('/[\x{FE00}-\x{FE0F}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}\x{2728}\x{274C}\x{274E}\x{2705}\x{2747}]/u', '', $str);
        return trim($str);
    }
    
    /**
     * 标准化 Telegram HTML 格式
     * 确保所有 HTML 标签符合 Telegram HTML 规范
     * 
     * Telegram HTML 支持的标签格式说明：
     * - 粗体：<b>文字内容</b> 或 <strong>文字内容</strong> （推荐使用 <b>）
     * - 斜体：<i>文字内容</i> 或 <em>文字内容</em> （推荐使用 <i>）
     * - 下划线：<u>文字内容</u> 或 <ins>文字内容</ins> （推荐使用 <u>）
     * - 删除线：<s>文字内容</s> 或 <strike>文字内容</strike> 或 <del>文字内容</del> （推荐使用 <s>）
     * - 遮挡码：<span class="tg-spoiler">文字内容</span> 或 <tg-spoiler>文字内容</tg-spoiler> （推荐使用 <tg-spoiler>）
     * - 超链接：<a href="链接地址">文字内容</a>
     * - TG用户链接：<a href="tg://user?id=123456789">文字内容</a>
     * - 等宽(点击复制)：<code>文字内容</code>
     * - 多行等宽(点击复制)：<pre>文字内容</pre>
     * - 代码块(点击复制)：<pre><code class="language-python">文字内容</code></pre>
     * 
     * 注意事项：
     * - 所有标签必须正确闭合
     * - 标签可以嵌套（如 <b><i>文字</i></b> 是允许的）
     * - 特殊字符在标签外需要转义：< 转义为 &lt;，> 转义为 &gt;，& 转义为 &amp;
     * - 换行使用 \n，Telegram HTML 模式会自动处理换行
     * 
     * @param string $text 需要标准化的文本
     * @return string 标准化后的文本
     */
    public function normalizeTelegramHtml($text)
    {
        if(empty($text)){
            return $text;
        }
        
        // 1. 将字面量的 \n（两个字符：反斜杠+n）转换为真正的换行符（一个字符）
        // 数据库存储的 \n 可能是字面量字符串，需要转换
        $text = str_replace('\\n', "\n", $text);
        
        // 2. 统一换行符：将 \r\n 和 \r 统一转换为 \n
        // Telegram HTML 模式支持 \n 自动换行，不需要转换为 <br>
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // 3. 标准化 HTML 标签：将不规范的标签转换为 Telegram 推荐格式
        // <strong> -> <b>, <em> -> <i>, <ins> -> <u>, <strike>/<del> -> <s>
        $text = preg_replace('/<strong\b([^>]*)>(.*?)<\/strong>/is', '<b$1>$2</b>', $text);
        $text = preg_replace('/<em\b([^>]*)>(.*?)<\/em>/is', '<i$1>$2</i>', $text);
        $text = preg_replace('/<ins\b([^>]*)>(.*?)<\/ins>/is', '<u$1>$2</u>', $text);
        $text = preg_replace('/<strike\b([^>]*)>(.*?)<\/strike>/is', '<s$1>$2</s>', $text);
        $text = preg_replace('/<del\b([^>]*)>(.*?)<\/del>/is', '<s$1>$2</s>', $text);
        
        // 4. 标准化遮挡码标签
        $text = preg_replace('/<span\s+class=["\']tg-spoiler["\']\s*>(.*?)<\/span>/is', '<tg-spoiler>$1</tg-spoiler>', $text);
        
        // 5. 清理代码块中的 class 属性（Telegram 不支持 class 属性）
        // <pre><code class="language-xxx"> -> <pre><code>
        $text = preg_replace('/<code\s+class=["\'][^"\']*["\']\s*>/i', '<code>', $text);
        
        // 6. 确保所有标签正确闭合（Telegram 要求严格闭合）
        // 代码中已经正确使用了 <b>, <code>, <u> 等标签，符合 Telegram HTML 规范
        
        return $text;
    }
     
    //机器人通知
    public function getdata(Request $request)
    {
        // 记录所有 Webhook 请求（用于调试）
        \Log::info('Telegram Webhook: 收到请求', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'content_type' => $request->header('Content-Type'),
            'has_body' => !empty($request->getContent()),
        ]);
        
        #获取通知的机器人ID
        $bot_rid = $request->rid ?? $request->query('rid');
        
        if(empty($bot_rid)){
            \Log::error('Telegram Webhook: 缺少 rid 参数', [
                'request_all' => $request->all(),
                'request_query' => $request->query(),
                'request_json' => $request->json() ? $request->json()->all() : null,
                'request_content' => substr($request->getContent(), 0, 500), // 只记录前500字符
            ]);
            return $this->responseData(400, '缺少 rid 参数');
        }
        
        \Log::info('Telegram Webhook: 处理机器人消息', ['bot_rid' => $bot_rid]);
        
        $data = TelegramBot::where('rid', $bot_rid)->first();
        if(empty($data)){
            \Log::error('Telegram Webhook: 机器人不存在', ['bot_rid' => $bot_rid]);
            return $this->responseData(400, '数据不存在');
        }
        
        $telegram = new Api($data->bot_token);
        
        // 本地开发模式：如果没有 Webhook 数据，尝试使用 getUpdates
        if (env('APP_ENV') === 'local' || env('APP_ENV') === 'development') {
            // 检查是否有 Webhook 数据
            $webhookData = $request->all();
            if (empty($webhookData) || (empty($webhookData['message']) && empty($webhookData['callback_query']) && empty($webhookData['my_chat_member']))) {
                // 尝试使用 getUpdates 获取消息（仅用于测试）
                try {
                    $lastUpdateId = \Cache::get("telegram_last_update_id_{$bot_rid}", 0);
                    $updates = $telegram->getUpdates([
                        'offset' => $lastUpdateId + 1,
                        'timeout' => 1,
                        'limit' => 1
                    ]);
                    
                    if (!empty($updates)) {
                        $update = $updates[0];
                        $updateId = $update->getUpdateId();
                        $updateData = $update->toArray();
                        
                        // 将 update 数据合并到 request 中
                        if (isset($updateData['message'])) {
                            $request->merge(['message' => $updateData['message']]);
                        }
                        if (isset($updateData['callback_query'])) {
                            $request->merge(['callback_query' => $updateData['callback_query']]);
                        }
                        if (isset($updateData['my_chat_member'])) {
                            $request->merge(['my_chat_member' => $updateData['my_chat_member']]);
                        }
                        
                        \Cache::put("telegram_last_update_id_{$bot_rid}", $updateId, 3600);
                    }
                } catch (\Exception $e) {
                    // 忽略错误，继续使用 Webhook 方式
                }
            }
        }
        
        // Webhook 场景：Telegram 会直接 POST JSON 数据到 Webhook URL
        // 优先从 JSON body 中获取数据（这是 Telegram Webhook 的标准方式）
        $result = [];
        
        // 方法1: 尝试从 request->json() 获取（Laravel 自动解析 JSON）
        if ($request->isJson() && $request->json()) {
            $result = $request->json()->all();
            \Log::info('Telegram Webhook: 从 request->json() 获取数据', ['bot_rid' => $bot_rid]);
        }
        
        // 方法2: 如果方法1失败，尝试手动解析 JSON body
        if (empty($result) || (empty($result['message']) && empty($result['callback_query']) && empty($result['my_chat_member']) && empty($result['chat_join_request']))) {
            $content = $request->getContent();
            if (!empty($content)) {
                $jsonData = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($jsonData)) {
                    $result = $jsonData;
                    \Log::info('Telegram Webhook: 从手动解析 JSON 获取数据', ['bot_rid' => $bot_rid]);
                }
            }
        }
        
        // 方法3: 尝试从 request->all() 获取（表单数据或已解析的数据）
        if (empty($result) || (empty($result['message']) && empty($result['callback_query']) && empty($result['my_chat_member']) && empty($result['chat_join_request']))) {
            $allData = $request->all();
            if (!empty($allData) && (isset($allData['message']) || isset($allData['callback_query']) || isset($allData['my_chat_member']) || isset($allData['chat_join_request']))) {
                $result = $allData;
                \Log::info('Telegram Webhook: 从 request->all() 获取数据', ['bot_rid' => $bot_rid]);
            }
        }
        
        // 记录接收到的数据（用于调试）
        if (!empty($result)) {
            \Log::info('Telegram Webhook: 收到数据', [
                'bot_rid' => $bot_rid,
                'has_message' => !empty($result['message']),
                'has_callback_query' => !empty($result['callback_query']),
                'has_my_chat_member' => !empty($result['my_chat_member']),
                'has_chat_join_request' => !empty($result['chat_join_request']),
            ]);
            
            // 将解析后的数据合并到 request 中，以便后续代码可以正常访问
            // 这样 $request->message、$request->callback_query 等就能正常工作了
            $request->merge($result);
        }
        
        // 如果仍然没有数据，记录详细日志用于调试
        if (empty($result)) {
            \Log::warning('Telegram Webhook: 没有收到任何数据', [
                'bot_rid' => $bot_rid,
                'request_method' => $request->method(),
                'request_content_type' => $request->header('Content-Type'),
                'request_body_preview' => substr($request->getContent(), 0, 500), // 只记录前500字符
                'request_all_keys' => array_keys($request->all()),
            ]);
        }
        // llog($result);

        #异常处理
        try {
            //新用户关注,拉入群
            if(!empty($request['my_chat_member']['new_chat_member']['status'])){
                //如果是机器人,不处理
                if(!$request->my_chat_member['from']['is_bot']){
                    //private,supergroup,channel
                    $TelegramBotUserServices = new TelegramBotUserServices();
                    $userpara = [
                        'bot_rid' => $bot_rid,
                        'chattype' => $request->my_chat_member['chat']['type'],
                        'chatid' => $request->my_chat_member['chat']['id'],
                        'chatusername' => $request->my_chat_member['chat']['username'] ?? '',
                        'chattitle' => ($request->my_chat_member['chat']['first_name'] ?? '') . '' .($request->my_chat_member['chat']['last_name'] ?? ''),
                        'grouptitle' => $request->my_chat_member['chat']['title'] ?? '',
                        
                        'tguserid' => $request->my_chat_member['chat']['id'],
                        'tgusername' => $request->my_chat_member['chat']['username'] ?? '',
                        'tgusernickname' => ($request->my_chat_member['chat']['first_name'] ?? '') .' '. ($request->my_chat_member['chat']['last_name'] ?? ''),
                        'status' => $request->my_chat_member['new_chat_member']['status'] ?? '',
                    ];
                    $userreturn = $TelegramBotUserServices->userfollow($userpara);
                }
            }
            
            //改群名
            if(!empty($request['message']['new_chat_title'])){
                //如果是机器人,不处理
                if(!$request->message['from']['is_bot']){
                    $TelegramBotUserServices = new TelegramBotUserServices();
                    $userpara = [
                        'bot_rid' => $bot_rid,
                        'chattype' => $request->message['chat']['type'],
                        'chatid' => $request->message['chat']['id'],
                        'chatusername' => $request->message['chat']['username'] ?? '',
                        'newchattitle' => $request->message['new_chat_title'] ?? '',
                    ];
                    $userreturn = $TelegramBotUserServices->changenickname($userpara);
                }
            }
            
            $inlinecall = 'N';
            //如果是内联按钮回调消息
            if(isset($request->callback_query['data'])){
                $inlinecall = 'Y';
                $message = $request->callback_query['data'];
                $chatid = $request->callback_query['message']['chat']['id'];
            }elseif(isset($request->message['user_shared']['request_id']) || isset($request->message['chat_shared']['request_id'])){
                $requestId = isset($request->message['user_shared']['request_id']) ?$request->message['user_shared']['request_id']:$request->message['chat_shared']['request_id'];
                $shareId = isset($request->message['user_shared']['request_id']) ?$request->message['user_shared']['user_id']:$request->message['chat_shared']['chat_id'];
                switch ($requestId) {
                    case 1:
                        $replytext = "用户ID：";
                        break;
                    case 2:
                        $replytext = "群组ID：";
                        break;
                    case 3:
                        $replytext = "机器人ID：";
                        break;
                    case 4:
                        $replytext = "频道ID：";
                        break;
                    default:
                        $replytext = "unknown";
                        break;
                }
                
                if($replytext != 'unknown'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $result['message']['chat']['id'], 
                        'text' => $replytext."<code>".$shareId."</code>", 
                        'allow_sending_without_reply' => true,
                        'parse_mode' => 'HTML'
                    ]);
                    return '';
                }
            }
            
            //如果不是发送的消息,则判断是否新成员加群
            elseif(!isset($request->message['text'])){
                //如果是通过邀请链接进来的
                if(isset($request->chat_join_request['from'])){
                   //如果是机器人加入,不发送欢迎消息
                   if($request->chat_join_request['from']['is_bot']){
                       return '';
                    }
                    $newusername = str_replace(['-','=','@','#','$','%','(',')','>','<','&','/','\\'],' ',$result['chat_join_request']['from']['first_name']); 
                    $newusername = $this->filter_Emoji($newusername);
                    $newuserUsername = $result['chat_join_request']['from']['username'] ?? '';
                    $chatid = $result['chat_join_request']['chat']['id'];
                    
                    //自动审核通过群
                    if(isset($request->chat_join_request['user_chat_id'])){
                        $approveurl = 'https://api.telegram.org/bot'.$data->bot_token.'/approveChatJoinRequest?chat_id='.$chatid.'&user_id='.$request->chat_join_request['user_chat_id'];
                        Get_Curl($approveurl);
                    }
                }
                //普通方式进群
                else{
                    //如果不是新成员加入,不处理
                    if(!isset($request->message['new_chat_member'])){
                        return '';
                    }
                    //如果是机器人加入,不发送欢迎消息
                    if($result['message']['new_chat_member']['is_bot']){
                        return '';
                    }
                    
                    // $newuserid = $result['message']['new_chat_member']['id']; 
                    $newusername = str_replace(['-','=','@','#','$','%','(',')','>','<','&','/','\\'],' ',$result['message']['new_chat_member']['first_name']); 
                    $newusername = $this->filter_Emoji($newusername);
                    $newuserUsername = $result['message']['new_chat_member']['username'] ?? '';
                    $chatid = $result['message']['chat']['id'];

                }
    
                $keyreply = TelegramBotKeyreply::where('bot_rid', $bot_rid)->where('status',0)->where('key_type',2)->first();
                if(empty($keyreply)){
                    return '';
                }
                
                $replytext = $keyreply->reply_content;
                
                if (strpos($replytext, 'trxusdtrate') !== false || strpos($replytext, 'trxusdtwallet') !== false || strpos($replytext, 'tgbotadmin') !== false || strpos($replytext, 'trxusdtshownotes') !== false || strpos($replytext, 'tgbotname') !== false || strpos($replytext, 'trx10usdtrate') !== false || strpos($replytext, 'trx100usdtrate') !== false || strpos($replytext, 'trx1000usdtrate') !== false) {
                    //替换变量
                    $walletcoin = TransitWalletCoin::from('t_transit_wallet_coin as a')
                                ->join('t_transit_wallet as b','a.transit_wallet_id','b.rid')
                                ->where('b.bot_rid', $bot_rid)
                                ->where('in_coin_name','usdt')
                                ->where('out_coin_name','trx')
                                ->select('a.exchange_rate','b.receive_wallet','b.show_notes')
                                ->first();
                    if(!empty($walletcoin) || $data->bot_admin_username || $data->bot_username){
                        $paraData = [
                            'trxusdtrate' => $walletcoin->exchange_rate ?? '',
                            'trxusdtwallet' => $walletcoin->receive_wallet ?? '',
                            'tgbotadmin' => $data->bot_admin_username ?? '',
                            'trxusdtshownotes' => $walletcoin->show_notes ?? '',
                            'tgbotname' => '@' . $data->bot_username ?? '',
                            'trx10usdtrate' => bcmul($walletcoin->exchange_rate ?? 0, 10, 2) + 0,
                            'trx100usdtrate' => bcmul($walletcoin->exchange_rate ?? 0, 100, 2) + 0,
                            'trx1000usdtrate' => bcmul($walletcoin->exchange_rate ?? 0, 1000, 2) + 0,
                        ];
                        
                        //检查参数是否匹配
                        preg_match_all('/\${.*?}/', $replytext, $matches);
                        $params = $matches[0];
                        $values = [];
                        foreach ($params as $param) {
                            $key = str_replace(['${', '}'], '', $param);
                            $values[$param] = $paraData[$key];
                        }
                 
                        $replytext = strtr($replytext, $values);
                        //替换结束
                    }
                }
                
                $spantest = '欢迎 '. $newusername .' 加入群组！'. PHP_EOL;
                if($newuserUsername != '' && !empty($newuserUsername)){
                    $spantest = $spantest . '用户：@'. $newuserUsername. PHP_EOL;
                }
                
                $replytext = $spantest . $replytext;
                $replyphoto = $keyreply->reply_photo;
                
                //取键盘
                $keyboardList = TelegramBotKeyreplyKeyboard::from('t_telegram_bot_keyreply_keyboard as a')
                            ->join('t_telegram_bot_keyboard as b','a.keyboard_rid','b.rid')
                            ->where('a.bot_rid', $bot_rid)
                            ->where('a.keyreply_rid', $keyreply->rid)
                            ->where('b.status', 0)
                            ->select('b.keyboard_name','b.keyboard_type','b.inline_type','b.keyboard_value')
                            ->orderBy('b.seq_sn','desc')
                            ->get();
                //有键盘的时候显示
                if($keyboardList->count() > 0){
                    $keyboardone = [];
                    $keyboardtwo = [];
                    $keyboardthree = [];
                    $keyboard = [];
                    $s = 0;
                    
                    foreach ($keyboardList as $k => $v) {
                        //键盘
                        if($v->keyboard_type == 1 && !empty($v->keyboard_name)){
                            if(count($keyboardone) == 3){
                                if(count($keyboardtwo) == 3){
                                    array_push($keyboardthree,$v->keyboard_name);
                                }else{
                                    array_push($keyboardtwo,$v->keyboard_name);
                                }
                            }else{
                                array_push($keyboardone,$v->keyboard_name);
                            }
                            
                        //内联按钮
                        }elseif($v->keyboard_type == 2 && !empty($v->keyboard_name) && !empty($v->keyboard_value)){
                            //url
                            if($v->inline_type == 1){
                                $keyboardone['text'] = $v->keyboard_name;
                                $keyboardone['url'] = $v->keyboard_value;
                                
                            //回调
                            }else{
                                $keyboardone['text'] = $v->keyboard_name;
                                $keyboardone['callback_data'] = $v->keyboard_value;
                            }
                            
                            if(!empty($keyboard)){
                                if(count($keyboard[$s]) == 2){
                                    $s++;
                                }
                            }
                            
                            $keyboard[$s][] = $keyboardone;
                            $keyboardone = [];
                        }
                    }
                    
                    //键盘
                    if($keyboardList[0]['keyboard_type'] == 1){
                        array_push($keyboard,$keyboardone);
                        array_push($keyboard,$keyboardtwo);
                        array_push($keyboard,$keyboardthree);
                        
                        $reply_markup = Keyboard::make([
                            'keyboard' => $keyboard, 
                            'resize_keyboard' => true, 
                            'one_time_keyboard' => false,
                            'selective' => true
                        ]); 
                    
                    //内联按钮
                    }else{
                        $reply_markup = [
                            'inline_keyboard' => $keyboard
                        ];
                        $reply_markup = json_encode($reply_markup);
                    }
                    
                //没有键盘
                }else{
                    //键盘保持不变
                    $reply_markup = Keyboard::forceReply(['force_reply'=>false,'input_field_placeholder'=>""]);
                }
                
                if(!empty($replyphoto)){
                    $response = $telegram->sendPhoto([
                        'chat_id' => $chatid, 
                        'photo' => InputFile::create($replyphoto, 'demo'),
                        'caption' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                }else{
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'allow_sending_without_reply' => true,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $reply_markup
                    ]);
                }
                
                return '';
            }else{
                // 保留原始消息用于关键词匹配，只截取长度
                // 检查是否有文本消息
                if(!isset($result['message']['text'])){
                    return '';
                }
                $message = mb_substr($result['message']['text'],0,100);
                $chatid = $result['message']['chat']['id'];
            }
            
            //替换消息中的@机器人的字符串（但保留 emoji 和特殊字符用于关键词匹配）
            $messageForMatch = trim(str_replace('@'.$data->bot_username,'',$message));
            // 保存原始消息用于匹配（不过滤 emoji，因为关键词可能包含 emoji）
            $originalMessage = $messageForMatch;
            // 清理后的消息用于其他处理（但保留空格和换行）
            $cleanedMessage = str_replace(["'","<",">","&","\\"],'',$messageForMatch);
            // 不过滤 emoji，因为关键词可能包含 emoji（如 ❇️智能托管）
            $message = $cleanedMessage;
            
            #判断如果是/start命令,因为私聊机器人第一次发送的命令就是/start,则判断是否记录新成员
            // 使用清理后的消息判断命令（命令不包含 emoji）
            $commandMessage = $this->filter_Emoji($cleanedMessage);
            if($commandMessage == '/start'){
                //如果是机器人,不处理
                if(isset($result['message']['chat']['type'])){
                    if($result['message']['chat']['type'] == 'private'){
                        //private
                        $TelegramBotUserServices = new TelegramBotUserServices();
                        $userpara = [
                            'bot_rid' => $bot_rid,
                            'chattype' => $result['message']['chat']['type'],
                            'chatid' => $result['message']['chat']['id'],
                            'chatusername' => $result['message']['chat']['username'] ?? '',
                            'chattitle' => ($result['message']['chat']['first_name'] ?? '') . '' .($result['message']['chat']['last_name'] ?? ''),
                            'grouptitle' => '',
                            
                            'tguserid' => $result['message']['chat']['id'],
                            'tgusername' => $result['message']['chat']['username'] ?? '',
                            'tgusernickname' => ($result['message']['chat']['first_name'] ?? '') .' '. ($result['message']['chat']['last_name'] ?? ''),
                            'status' => 'member',
                        ];
                        $userreturn = $TelegramBotUserServices->userfollow($userpara);
                        
                        // 自动设置命令菜单（三条横杠按钮）
                        try {
                            $commandData = TelegramBotCommand::where('bot_rid', $bot_rid)->get();
                            if($commandData->count() > 0){
                                $commandsone = [];
                                foreach ($commandData as $k => $v) {
                                    if($v->chat_type == 1 || $v->chat_type == 0){ // 私聊或全部
                                        $commandone = [];
                                        $commandone['command'] = $v->command;
                                        $commandone['description'] = $v->description;
                                        $commandsone[] = $commandone;
                                    }
                                }
                                if(!empty($commandsone)){
                                    $encodedCommandsone = json_encode($commandsone);
                                    $sendmessageurl = "https://api.telegram.org/bot". $data->bot_token ."/setMyCommands?commands=".urlencode($encodedCommandsone)."&scope=".urlencode('{"type":"all_private_chats"}');
                                    $res = Get_Curl($sendmessageurl);
                                    \Log::info('自动设置命令菜单', [
                                        'bot_rid' => $bot_rid,
                                        'commands_count' => count($commandsone),
                                        'response' => $res,
                                    ]);
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('设置命令菜单失败', [
                                'bot_rid' => $bot_rid,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
            
            //如果是回复的消息,判断是否添加智能监控地址
            if(isset($request->message['reply_to_message']) && $request->message['reply_to_message']['from']['is_bot'] && mb_substr($message,0,1) == 'T' && mb_strlen($message) == 34){
                $replymessageid = $request->message['reply_to_message']['message_id'];
                #取缓存--添加托管地址
                $add_ai_address = getRedis('aitrusteeshipaddaddress'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('aitrusteeshipaddaddress'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '能量智能托管请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                            ],
                            [
                                ['text' => '⬅️返回上一步', 'callback_data' => 'aitrusteeship'],
                                ['text' => '🔄刷新', 'callback_data' => 'aitrusteeshipmyaddress']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    $blackdata = TransitWalletBlack::where('black_wallet', $message)->first();
                    if(!empty($blackdata)){
                        $replytext = '地址:<code>'.$message.'</code> 为禁止地址(黑钱包)，如果该地址为您的真实地址，请联系客服';
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = EnergyAiTrusteeship::where('wallet_addr',$message)->first();
                    
                    if(!empty($isexit)){
                        $replytext = "该地址已存在，如果您的托管地址列表不存在，请联系客服处理\n";
                        $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();
                    }else{
                        $insert_data = [];
                        $insert_data['bot_rid'] = $bot_rid;
                        $insert_data['tg_uid'] = $chatid;
                        $insert_data['wallet_addr'] = $message;
                        $insert_data['status'] = 0;
                        $insert_data['create_by'] = 0;	
                        $insert_data['create_time'] = nowDate();
                         
                        EnergyAiTrusteeship::insert($insert_data);
                        $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();

                        $replytext = "<b>智能托管地址已添加成功</b>\n"
                                ."当前已托管：".$aiWallet->count()." 个\n"
                                ."还可以托管：".(10 - $aiWallet->count())." 个\n";
                    }
                    
                    if($aiWallet->count() > 0){
                        $ico = array('0'=>'1️⃣','1'=>'2️⃣','2'=>'3️⃣' ,'3'=>'4️⃣','4'=>'5️⃣' ,'5'=>'6️⃣','6'=>'7️⃣','7'=>'8️⃣' ,'8'=>'9️⃣'  ,'9'=>'🔟' );
                        $i = 0;
                        foreach ($aiWallet as $k => $v) {
                            $replytext = $replytext
                                    .$ico[$i]."  <code>".$v->wallet_addr. "</code>\n";
                            $i++;
                        }
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--删除托管地址
                $add_ai_address = getRedis('aitrusteeshipdeleteaddress'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('aitrusteeshipdeleteaddress'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '能量智能托管请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = EnergyAiTrusteeship::where('wallet_addr',$message)->where('tg_uid',$chatid)->first();
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                            ],
                            [
                                ['text' => '⬅️返回上一步', 'callback_data' => 'aitrusteeship'],
                                ['text' => '🔄刷新', 'callback_data' => 'aitrusteeshipmyaddress']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    if(empty($isexit)){
                        $replytext = "该地址不存在，请输入该账号下的托管地址\n";
                        $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();
                    }else{
                        EnergyAiTrusteeship::where('rid', $isexit->rid)->delete();
                        $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();
                        
                        $replytext = "<b>智能托管地址已删除成功</b>\n"
                                ."当前已托管：".$aiWallet->count()." 个\n"
                                ."还可以托管：".(10 - $aiWallet->count())." 个\n";
                    }
                    
                    if($aiWallet->count() > 0){
                        $ico = array('0'=>'1️⃣','1'=>'2️⃣','2'=>'3️⃣' ,'3'=>'4️⃣','4'=>'5️⃣' ,'5'=>'6️⃣','6'=>'7️⃣','7'=>'8️⃣' ,'8'=>'9️⃣'  ,'9'=>'🔟' );
                        $i = 0;
                        foreach ($aiWallet as $k => $v) {
                            $replytext = $replytext
                                    .$ico[$i]."  <code>".$v->wallet_addr. "</code>\n";
                            $i++;
                        }
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--添加监控地址
                $add_ai_address = getRedis('monitorddaddress'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('monitorddaddress'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '监控钱包请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '➕添加监控地址', 'callback_data' => 'monitorddaddress'],
                                ['text' => '➖删除监控地址', 'callback_data' => 'monitordeleteaddress']
                            ],
                            [
                                ['text' => '⬅️返回上一步', 'callback_data' => 'monitorwallet_1'],
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    $blackdata = TransitWalletBlack::where('black_wallet', $message)->first();
                    if(!empty($blackdata)){
                        $replytext = '地址:<code>'.$message.'</code> 为禁止地址(黑钱包)，如果该地址为您的真实地址，请联系客服';
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = MonitorWallet::where('monitor_wallet',$message)->first();
                    
                    if(!empty($isexit)){
                        $replytext = "该地址已存在，如果您的监控地址列表不存在，请联系客服处理\n"
                                    ."最近添加：\n";
                        $aiWallet = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid)->orderBy('rid','desc')->get();
                    }else{
                        //查用户的余额
                        $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                        
                        $insert_data = [];
                        $insert_data['bot_rid'] = $bot_rid;
                        $insert_data['chain_type'] = 'trc';
                        $insert_data['monitor_wallet'] = $message;
                        $insert_data['tg_notice_obj'] = $chatid;
                        $insert_data['status'] = 0;
                        $insert_data['create_time'] = nowDate();
                         
                        MonitorWallet::insert($insert_data);
                        $aiWallet = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid)->orderBy('rid','desc')->get();

                        $replytext = "<b>监控钱包地址已添加成功</b>\n"
                                ."当前已监控：".$aiWallet->count()." 个\n"
                                ."还可以监控：".($botuser->max_monitor_wallet - $aiWallet->count())." 个\n"
                                ."最近添加：\n";
                    }
                    
                    if($aiWallet->count() > 0){
                        foreach ($aiWallet as $k => $v) {
                            $replytext = $replytext."  <code>".$v->monitor_wallet. "</code>\n";
                        }
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--删除监控地址
                $add_ai_address = getRedis('monitordeleteaddress'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('monitordeleteaddress'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '监控钱包请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = MonitorWallet::where('monitor_wallet',$message)->where('tg_notice_obj',$chatid)->first();
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '➕添加监控地址', 'callback_data' => 'monitorddaddress'],
                                ['text' => '➖删除监控地址', 'callback_data' => 'monitordeleteaddress']
                            ],
                            [
                                ['text' => '⬅️返回上一步', 'callback_data' => 'monitorwallet_1'],
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    if(empty($isexit)){
                        $replytext = "该地址不存在，请输入该账号下的监控地址\n"."最近添加：\n";
                        $aiWallet = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid)->orderBy('rid','desc')->get();
                    }else{
                        //查用户的余额
                        $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                        
                        MonitorWallet::where('rid', $isexit->rid)->delete();
                        $aiWallet = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid)->orderBy('rid','desc')->get();
                        
                        $replytext = "<b>监控地址已删除成功</b>\n"
                                ."当前已监控：".$aiWallet->count()." 个\n"
                                ."还可以监控：".($botuser->max_monitor_wallet - $aiWallet->count())." 个\n"
                                ."最近添加：\n";
                    }
                    
                    if($aiWallet->count() > 0){
                        foreach ($aiWallet as $k => $v) {
                            $replytext = $replytext."  <code>".$v->monitor_wallet. "</code>\n";
                        }
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--查询地址购买的能量笔数套餐剩余笔数
                $add_ai_address = getRedis('energybishusyconfirm'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('energybishusyconfirm'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '查询地址购买的能量笔数套餐剩余笔数请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = EnergyAiBishu::where('bot_rid',$bot_rid)->where('wallet_addr',$message)->first();
                    
                    if(empty($isexit)){
                        $replytext = "查询地址：<code>".$message."</code>\n"
                                    ."查询结果：还未购买能量笔数套餐！";
                    }else{
                        //查询是否第三方平台代理
                        $platformBot = EnergyPlatformBot::where("bot_rid",$bot_rid)->first();
                        if(empty($platformBot) || $platformBot->bishu_daili_type == 1){
                            $replytext = "查询地址：<code>".$message."</code>\n"
                                    ."总充值：".$isexit->total_buy_usdt." USDT\n"
                                    ."剩余次数：".($isexit->max_buy_quantity - $isexit->total_buy_quantity);
                        }else{
                            $energyPlatform = EnergyPlatform::where('rid',$isexit->energy_platform_rid)->first();
                            if(!empty($energyPlatform)){
                                //查第三方平台的笔数
                                $balance_url = 'https://trongas.io/api/auto/data';
                                $param1 = [
                                    "orderId" => '',
                                    "state" => '',
                                    "receiveAddress" => $message,
                                    "chromeIndex" => ''
                                ];
                                $param = [
                                    "page" => 1,
                                    "username" => $energyPlatform->platform_uid,
                                    "search" => $param1
                                ];
                                $headerArray = array("Content-Type: application/json;charset='utf-8'");
                                $param = json_encode($param);
                                $platformRes = curl_post_https($balance_url,$param,$headerArray);
                                
                                if(empty($platformRes)){
                                    $replytext = "查询地址：<code>".$message."</code>\n"
                                        ."查询结果：查询失败1，请联系客服！";
                                }else{
                                    $platformRes = json_decode($platformRes,true);
                                    if(isset($platformRes['code']) && $platformRes['code'] && $platformRes['code'] == 10000){
                                        if(isset($platformRes['data']['data']) && count($platformRes['data']['data']) > 0){
                                            $syCount = $platformRes['data']['data'][0]['freeze'] == 1 ?0:$platformRes['data']['data'][0]['residue'];
                                            $replytext = "查询地址：<code>".$message."</code>\n"
                                                    ."总充值：".$isexit->total_buy_usdt." USDT\n"
                                                    ."剩余次数：".($syCount + ($isexit->max_buy_quantity - $isexit->total_buy_quantity));
                                        }else{
                                            $replytext = "查询地址：<code>".$message."</code>\n"
                                                    ."总充值：".$isexit->total_buy_usdt." USDT\n"
                                                    ."剩余次数：".($isexit->max_buy_quantity - $isexit->total_buy_quantity)." 次";
                                        }
                                        
                                    }else{
                                        $replytext = "查询地址：<code>".$message."</code>\n"
                                        ."查询结果：查询失败2，请联系客服！";
                                    }
                                }
                            }else{
                                $replytext = "查询地址：<code>".$message."</code>\n"
                                        ."总充值：".$isexit->total_buy_usdt." USDT\n"
                                        ."剩余次数：".($isexit->max_buy_quantity - $isexit->total_buy_quantity);
                            }
                        }
                    }
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '🔄重新查询', 'callback_data' => 'energybishusy']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--笔数套餐绑定通知
                $add_ai_address = getRedis('energybishubind'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('energybishubind'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '笔数套餐绑定通知请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = EnergyAiBishu::where('bot_rid',$bot_rid)->where('wallet_addr',$message)->first();
                    
                    if(empty($isexit)){
                        $replytext = "绑定地址：<code>".$message."</code>\n"
                                    ."绑定结果：还未购买能量笔数套餐，请先购买后再添加通知！";
                    }elseif(empty($isexit->tg_uid)){
                        //为空时,才可以绑定
                        EnergyAiBishu::where('bot_rid',$bot_rid)->where('wallet_addr',$message)->update(["tg_uid" => $chatid]);
                        $replytext = "绑定地址：<code>".$message."</code>\n"
                                    ."绑定结果：恭喜您，绑定成功！";
                    }elseif($isexit->tg_uid == $chatid){
                        $replytext = "绑定地址：<code>".$message."</code>\n"
                                    ."绑定结果：您已绑定该地址，无需重复绑定！";
                    }else{
                        $replytext = "绑定地址：<code>".$message."</code>\n"
                                    ."绑定结果：该地址已被其他用户绑定，如果该地址是您的地址，请联系客服处理！";
                    }
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '🖌笔数套餐', 'callback_data' => 'energybishu']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--用户绑定钱包
                $add_ai_address = getRedis('userbindaddressconfirm'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('userbindaddressconfirm'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '绑定钱包通知请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = TelegramBotUser::where('bot_rid',$bot_rid)->where('bind_trc_wallet_addr',$message)->where('tg_uid','<>',$chatid)->first();
                    
                    if($isexit){
                        $replytext = "绑定地址：<code>".$message."</code>\n"
                                    ."绑定结果：该地址已被其他用户绑定，如果该地址是您的地址，请联系客服处理！";
                    }else{
                        TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->update(['bind_trc_wallet_addr' => $message]);
                        $replytext = "绑定地址：<code>".$message."</code>\n"
                                    ."绑定结果：绑定成功";
                    }
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '🕐重新开始', 'callback_data' => '/start']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--用户增加笔数
                $add_ai_address = getRedis('energybishubalanceaddconfirm'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('energybishubalanceaddconfirm'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '该功能请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = EnergyAiBishu::where('bot_rid',$bot_rid)->where('wallet_addr',$message)->where('tg_uid','<>',$chatid)->first();
                    
                    if($isexit){
                        $replytext = "增加笔数地址：<code>".$message."</code>\n"
                                    ."增加结果：该地址已被其他用户绑定，如果该地址是您的地址，请联系客服处理！";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }else{
                        $replytext = "<b>请在2分钟内回复此消息，增加笔数次数。比如 100 表示给地址增加 100 次</b>\n"
                                    ."增加地址：<code>".$message."</code>\n\n"
                                    ."点击 /start 重新开始";
                        
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入增加的笔数,例如100"]);
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    
                    #设置缓存
                    $add_ai_address = getRedis('energybishubalanceaddconfirmed'.$chatid);
                    if(!empty($add_ai_address)){
                        deleteRedis('energybishubalanceaddconfirmed'.$chatid);
                    }
                    setexRedis('energybishubalanceaddconfirmed'.$chatid,$response['message_id']."_".$message,120);
                    return '';
                }
                
                #取缓存--用户给其他地址购买能量
                $add_ai_address = getRedis('balancebuyotherconfirm'.$chatid);
                if(!empty($add_ai_address)){
                    $explodeArr = explode("_", $add_ai_address);
                    $messageid = implode("_", array_slice($explodeArr, 0, 1));
                    $rid = implode("_", array_slice($explodeArr, 1,1));
                    $paytype = implode("_", array_slice($explodeArr, 2));
                    
                    if($replymessageid == $messageid){
                        deleteRedis('balancebuyotherconfirm'.$chatid);
                    
                        //该命令只能私聊机器人
                        if(mb_substr($chatid,0,1) == '-'){
                            $response = $telegram->sendMessage([
                                'chat_id' => $chatid, 
                                'text' => '该功能请私聊机器人！', 
                                'reply_to_message_id' => $result['message']['message_id'],
                                'parse_mode' => 'MarkDown',
                                'allow_sending_without_reply' => true
                            ]);
                            return '';
                        }
                        
                        //查用户的余额
                        $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                        if(empty($botuser)){
                            $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                            
                            //内联按钮
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                    ]
                                ]
                            ];
                            $reply_markup = json_encode($keyboard);
                        }else{
                            $packageData = EnergyPlatformPackage::from('t_energy_platform_package as a')
                                ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                                ->where('a.rid', $rid)
                                ->where('a.status',0)
                                ->where('b.status',0)
                                ->select('a.rid','a.package_name','a.energy_amount','a.trx_price','a.usdt_price','a.energy_day')
                                ->first();
                            if(empty($packageData)){
                                $replytext = "<b>您好！该套餐已暂停购买，请选择其他套餐！</b>\n";
                                $keyboard = [
                                    'inline_keyboard' => [
                                        [
                                            ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy']
                                        ]
                                    ]
                                ];
                                
                                $reply_markup = json_encode($keyboard);
                            }else{
                                if($paytype == 'trx' && floatval($botuser->cash_trx) >= floatval($packageData->trx_price)){
                                    TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->update(['cash_trx' => $botuser->cash_trx - $packageData->trx_price]);
                                    llog($packageData->energy_day);
                                    EnergyQuickOrder::insert([
                                        'bot_rid' => $bot_rid,    
                                        'tg_uid' => $chatid,    
                                        'wallet_addr' => $message,    
                                        'energy_amount' => $packageData->energy_amount,    
                                        'energy_day' => $packageData->energy_day,  
                                        'package_name' => $packageData->package_name,
                                        'package_rid' => $packageData->rid,
                                        'status' => 1,    
                                        'pay_price' => $packageData->trx_price,
                                        'pay_type' => 'trx',    
                                        'pay_time' => nowDate()
                                    ]);
                                    
                                    $replytext ="✅恭喜您，购买成功！能量将在5秒内到账\n" 
                                                ."购买地址：<code>".$message."</code>\n"
                                                ."购买套餐：".$packageData->package_name."\n"
                                                ."购买能量：".$packageData->energy_amount."\n"
                                                ."支付方式：".$packageData->trx_price." TRX\n\n"
                                                ."剩余可用余额：".($botuser->cash_trx - $packageData->trx_price)." TRX ".$botuser->cash_usdt." USDT";
                                    $keyboard = [
                                        'inline_keyboard' => [
                                            [
                                                ['text' => '🔄重新购买套餐', 'callback_data' => '/buyenergy']
                                            ]
                                        ]
                                    ];
                                    
                                    $reply_markup = json_encode($keyboard);
                                }elseif($paytype == 'usd' && floatval($botuser->cash_usdt) >= floatval($packageData->usdt_price)){
                                    TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->update(['cash_usdt' => $botuser->cash_usdt - $packageData->usdt_price]);
                                    EnergyQuickOrder::insert([
                                        'bot_rid' => $bot_rid,    
                                        'tg_uid' => $chatid,    
                                        'wallet_addr' => $message,    
                                        'energy_amount' => $packageData->energy_amount,    
                                        'energy_day' => $packageData->energy_day,  
                                        'package_name' => $packageData->package_name,
                                        'package_rid' => $packageData->rid,
                                        'status' => 1,    
                                        'pay_price' => $packageData->usdt_price,
                                        'pay_type' => 'usdt',    
                                        'pay_time' => nowDate()
                                    ]);
           
                                    $replytext ="✅恭喜您，购买成功！能量将在5秒内到账\n" 
                                                ."购买地址：<code>".$message."</code>\n"
                                                ."购买套餐：".$packageData->package_name."\n"
                                                ."购买能量：".$packageData->energy_amount."\n"
                                                ."支付方式：".$packageData->usdt_price." USDT\n\n"
                                                ."剩余可用余额：".$botuser->cash_trx." TRX ".($botuser->cash_usdt - $packageData->usdt_price)." USDT";
                                    $keyboard = [
                                        'inline_keyboard' => [
                                            [
                                                ['text' => '🔄重新购买套餐', 'callback_data' => '/buyenergy']
                                            ]
                                        ]
                                    ];
                                    
                                    $reply_markup = json_encode($keyboard);
                                }else{
                                    $replytext = "<b>❌余额不足，请及时充值！</b>\n"
                                                ."TRX 余额：".$botuser->cash_trx."\n"
                                                ."USDT 余额：".$botuser->cash_usdt."\n\n"
                                                ."当前套餐需要：".$packageData->trx_price." TRX 或者：".$packageData->usdt_price." USDT";
                                    $keyboard = [
                                        'inline_keyboard' => [
                                            [
                                                ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy'],
                                                ['text' => '💵充值余额', 'callback_data' => 'aitrusteeshiprechargetrx']
                                            ]
                                        ]
                                    ];
                                    
                                    $reply_markup = json_encode($keyboard);
                                }
                                
                            }
                        }
                        
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $reply_markup
                        ]);
                        
                        return '';
                    }
                }
            }
            
            //如果是回复的消息,判断是否添加备注等
            if(isset($request->message['reply_to_message']) && $request->message['reply_to_message']['from']['is_bot'] && mb_strlen($message) >= 1){
                $replymessageid = $request->message['reply_to_message']['message_id'];

                #取缓存--修改监控地址备注
                $add_ai_address = getRedis('monitorwalletupdatewalletconfirm'.$chatid);
                $explodeArr = explode("_", $add_ai_address);
                $redisMessageId = implode("_", array_slice($explodeArr, 0, 1));
                $redisWalletRid = implode("_", array_slice($explodeArr, 1));
                
                if(!empty($add_ai_address) && $replymessageid == $redisMessageId){
                    deleteRedis('monitorwalletupdatewalletconfirm'.$chatid);
               
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '监控钱包修改请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //查下地址是否已存在
                    $isexit = MonitorWallet::where('rid',$redisWalletRid)->where('tg_notice_obj',$chatid)->first();
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '➕添加监控地址', 'callback_data' => 'monitorddaddress'],
                                ['text' => '➖删除监控地址', 'callback_data' => 'monitordeleteaddress']
                            ],
                            [
                                ['text' => '⬅️返回上一步', 'callback_data' => 'monitorwallet_1'],
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ],
                            [
                                ['text' => '🛠修改备注', 'callback_data' => 'monitorwalletupdate_1']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    if(empty($isexit)){
                        $replytext = "该地址不存在，请重新选择该账号下的监控地址\n";
                    }else{
                        MonitorWallet::where('rid',$redisWalletRid)->update(['comments' => mb_substr($message,0,50)]);
                        
                        $replytext = "<b>监控地址备注已修改</b>\n"
                                ."当前监控地址：".$isexit->monitor_wallet."\n"
                                ."当前备注：".mb_substr($message,0,50);
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
            }
            
            //如果是回复的消息,且回复的纯数字,则判断是否是余额usdt兑换trx 或者 增加笔数次数
            if(isset($request->message['reply_to_message']) && $request->message['reply_to_message']['from']['is_bot'] && ctype_digit($message)){
                $replymessageid = $request->message['reply_to_message']['message_id'];
                #取缓存--余额USDT转换为TRX
                $add_ai_address = getRedis('aitrusteeshipusdtswaptrx'.$chatid);

                if(!empty($add_ai_address) && $replymessageid == $add_ai_address){
                    deleteRedis('aitrusteeshipusdtswaptrx'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '能量智能托管请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔥TRX充值', 'callback_data' => 'aitrusteeshiprechargetrx'],
                                ['text' => '🔥USDT充值', 'callback_data' => 'aitrusteeshiprechargeusdt'],
                                ['text' => '🔀U转TRX', 'callback_data' => 'aitrusteeshipusdtswaptrx']
                            ],
                            [
                                ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                            ],
                            [
                                ['text' => '👑我的托管地址', 'callback_data' => 'aitrusteeshipmyaddress']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    //查用户的余额
                    $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                    if(empty($botuser)){
                        $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                        
                    }else{
    
                        if($botuser['cash_usdt'] >= 1 && floatval($botuser['cash_usdt']) >= floatval($message)){
                            //查询汇率
                            $okxurl = 'https://www.okx.com/api/v5/market/index-tickers?instId=TRX-USDT&quoteCcy=USDT';
        
                            $okxdata = Get_Curl($okxurl,'',[],5); //5秒超时
                    
                            if(!empty($okxdata)){
                                $okxdata = json_decode($okxdata,true);
                                if(isset($okxdata['data']) && count($okxdata['data']) > 0){
                                    $trxusdt = $okxdata['data'][0]['idxPx'];
                                    $usdttrx = number_format(1 / $trxusdt, 2);
                                    $realrate = bcmul($usdttrx, (1 - 0.05),2); //按百分比收手续费5%
                                    
                                    //扣款并转换
                                    $save_data = [];
                                    $save_data['cash_trx'] = bcadd($botuser['cash_trx'], $message * $realrate,6);
                                    $save_data['cash_usdt'] = bcsub($botuser['cash_usdt'], $message,6);
                                    TelegramBotUser::where('rid',$botuser['rid'])->update($save_data);
                                    
                                    //查用户的余额
                                    $botuser = TelegramBotUser::where('rid',$botuser['rid'])->first();
                                    
                                    $replytext = "<b>恭喜您！USDT 转 TRX 成功！</b>\n" 
                                                ."<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                                                ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                                                ."⚠️TRX余额不足时，不再执行智能托管，请及时充值\n"
                                                ."⚠️充值的USDT可点击下方转换为TRX\n\n"
                                                ."<b>请保证余额充足,点击下方可充值余额！</b>";
                                                
                                }else{
                                    $replytext = "<b>获取汇率失败，请重试1</b>\n";
                                }
                            }else{
                                $replytext = "<b>获取汇率失败，请重试2</b>\n";
                            }
                        }else{
                            $replytext = "<b>转换失败，请充值后转换，当前USDT余额：</b>".$botuser['cash_usdt']."\n"
                                        ."USDT大于1时才能转换为TRX！";
                        }
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
                
                #取缓存--用户增加笔数-最后增加
                $add_ai_address = getRedis('energybishubalanceaddconfirmed'.$chatid);
                $explodeArr = explode("_", $add_ai_address);
                $redisMessageId = implode("_", array_slice($explodeArr, 0, 1));
                $redisWalletAddr = implode("_", array_slice($explodeArr, 1));

                if(!empty($add_ai_address) && $replymessageid == $redisMessageId && $message > 0){
                    deleteRedis('energybishubalanceaddconfirmed'.$chatid);
                    
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '该功能请私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    
                    //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "⚠️用户信息为空，请发送 /start 初始化用户，然后再执行添加命令";
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }else{
                    //查机器人笔数单价
                    $energyBot = EnergyPlatformBot::where('bot_rid',$bot_rid)->first();
                    if(!isset($energyBot) || $energyBot->per_bishu_usdt_price <= 0){
                        $replytext = "⚠️机器人能量笔数未配置，请联系客服";
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }else{
                        $biWallet = EnergyAiBishu::where('wallet_addr',$redisWalletAddr)->first();
                        //地址被添加过不允许重复添加
                        if(isset($biWallet) && $biWallet->bot_rid != $bot_rid && $biWallet->tg_uid != $chatid){
                            $response = $telegram->sendMessage([
                                'chat_id' => $chatid, 
                                'text' => '❌地址：'.$redisWalletAddr.' 在机器人已存在，请联系管理处理！', 
                                'reply_to_message_id' => $result['message']['message_id'],
                                'parse_mode' => 'MarkDown',
                                'allow_sending_without_reply' => true
                            ]);
                            return '';
                        }
                        
                        $kouPrice = $message * $energyBot->per_bishu_usdt_price;
                        if($botuser->cash_usdt < $kouPrice){
                            $replytext = "❌用户USDT余额不足，当前余额：".($botuser->cash_usdt + 0)." USDT，需要：".$kouPrice." USDT，请先在机器人充值USDT！";
                            $response = $telegram->sendMessage([
                                'chat_id' => $chatid, 
                                'text' => $replytext, 
                                'reply_to_message_id' => $result['message']['message_id'],
                                'parse_mode' => 'MarkDown',
                                'allow_sending_without_reply' => true
                            ]);
                            return '';
                        }else{
                            TelegramBotUser::where('rid',$botuser->rid)->update(['cash_usdt' => $botuser->cash_usdt - $kouPrice]);
                        }
                    }
                }
                
                //如果存在则增加次数
                if($biWallet){
                    $oldCishu = $biWallet->max_buy_quantity;
                    $res = EnergyAiBishu::where('rid',$biWallet->rid)->update(['max_buy_quantity' => $biWallet->max_buy_quantity + $message,'update_time' => nowDate(),
                            'total_buy_usdt' => $biWallet->total_buy_usdt + $kouPrice,'tg_uid' => $chatid]);
                }else{
                    $oldCishu = 0;
                    $res = EnergyAiBishu::create([
                        'bot_rid' => $bot_rid,
                        'wallet_addr' => $redisWalletAddr,
                        'tg_uid' => $chatid,
                        'total_buy_usdt' => $kouPrice,
                        'max_buy_quantity' => $message,
                        'total_buy_quantity' => 0,
                        'back_comments' => '',
                        'create_time' => nowDate(),
                        'is_buy' => 'Y'
                    ]);
                }
                
                $replytext = "地址：".$redisWalletAddr.($res ? " 添加成功" : " 添加失败")."\n"
                            ."原购买总次数：".$oldCishu."\n"
                            ."本次新增次数：".$message."\n\n"
                            ."扣除USDT：".$kouPrice." USDT\n"
                            ."点击发送 /start 重新开始";
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                
                return '';
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                }
            }
            
            #判断message如果是T开头,0x开头,则查询波场地址余额
            if(mb_substr($message,0,1) == 'T' && mb_strlen($message) == 34){
                $replytext = $this->querytronbalance($message);
                
                //内联按钮
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '链上查询', 'url' => 'https://tronscan.org/#/address/'.$message],
                            ['text' => '查授权', 'callback_data' => '查授权'.$message],
                            ['text' => '关于机器人', 'url' => 'https://t.me/'.$data->bot_username]
                        ],
                        [
                            ['text' => '兑换TRX', 'callback_data' => '兑换'],
                            ['text' => 'USDT记录', 'callback_data' => 'searchusdtlistall'.$message],
                            ['text' => 'TRX记录', 'callback_data' => 'searchtrxalistall'.$message]
                        ]
                    ]
                ];
                $encodedKeyboard = json_encode($keyboard);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
            
            #查地址USDT或者TRX交易记录
            }elseif(in_array(mb_substr($message,0,17),['searchusdtlistall','searchtrxalistall','searchusdtlistain','searchusdtlistout','searchtrxalistain','searchtrxalistout']) && mb_strlen($message) == 51){
                $walletaddr = mb_substr($message,17);
                $type = mb_substr($message,0,17);
                $replytext = $this->searchwalletjylist($walletaddr,$type);
                
                if(in_array($type,['searchusdtlistall','searchusdtlistain','searchusdtlistout'])){
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔍链上查询', 'url' => 'https://tronscan.org/#/address/'.$walletaddr],
                                ['text' => 'TRX记录', 'callback_data' => 'searchtrxalistall'.$walletaddr]
                            ],
                            [
                                ['text' => '➕仅看USDT收入', 'callback_data' => 'searchusdtlistain'.$walletaddr],
                                ['text' => '➖仅看USDT支出', 'callback_data' => 'searchusdtlistout'.$walletaddr]
                            ]
                        ]
                    ];
                }else{
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔍链上查询', 'url' => 'https://tronscan.org/#/address/'.$walletaddr],
                                ['text' => 'USDT记录', 'callback_data' => 'searchusdtlistall'.$walletaddr]
                            ],
                            [
                                ['text' => '➕仅看TRX收入', 'callback_data' => 'searchtrxalistain'.$walletaddr],
                                ['text' => '➖仅看TRX支出', 'callback_data' => 'searchtrxalistout'.$walletaddr]
                            ]
                        ]
                    ];
                }
                
                $encodedKeyboard = json_encode($keyboard);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
                
            //查以太系列余额
            }elseif(in_array(mb_substr($message,0,2),['0X','0x']) && mb_strlen($message) == 42){
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => '❌查询中'.$message.'，请耐心等待！请勿重复查询！', 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                
                $replytext = $this->queryercbalance($message);
                
                //内联按钮
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '以太链上查询', 'url' => 'https://etherscan.io/address/'.$message]
                        ],
                        [
                            ['text' => '币安链上查询', 'url' => 'https://bscscan.com/address/'.$message]
                        ],
                        [
                            ['text' => '欧易链上查询', 'url' => 'https://www.oklink.com/cn/okc/address/'.$message]
                        ]
                    ]
                ];
                $encodedKeyboard = json_encode($keyboard);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
                
            //会员添加笔数
            }elseif(in_array(mb_substr($message,0,4),['添加笔数','增加笔数'])){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $message = trim(str_replace(['添加笔数','增加笔数'],'',$message));
                
                $message = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);
                
                if(!isset($message[0]) || !isset($message[1]) || (isset($message[0]) && mb_substr($message[0],0,1) != 'T' && mb_strlen($message[0]) != 34) || (isset($message[1])) && (!ctype_digit($message[1]) || $message[1] <= 0)){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '⚠️请输入格式：添加笔数 Tdfal3432xxxxxx 100 ，表示给地址增加100笔免费转账！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "⚠️用户信息为空，请发送 /start 初始化用户，然后再执行添加命令";
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }else{
                    //查机器人笔数单价
                    $energyBot = EnergyPlatformBot::where('bot_rid',$bot_rid)->first();
                    if(!isset($energyBot) || $energyBot->per_bishu_usdt_price <= 0){
                        $replytext = "⚠️机器人能量笔数未配置，请联系客服";
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }else{
                        $biWallet = EnergyAiBishu::where('wallet_addr',$message[0])->first();
                        //地址被添加过不允许重复添加
                        if(isset($biWallet) && $biWallet->bot_rid != $bot_rid){
                            $response = $telegram->sendMessage([
                                'chat_id' => $chatid, 
                                'text' => '❌地址：'.$message[0].' 在后台已存在，请联系管理处理！', 
                                'reply_to_message_id' => $result['message']['message_id'],
                                'parse_mode' => 'MarkDown',
                                'allow_sending_without_reply' => true
                            ]);
                            return '';
                        }
                        
                        $kouPrice = $message[1] * $energyBot->per_bishu_usdt_price;
                        if($botuser->cash_usdt < $kouPrice){
                            $replytext = "❌用户USDT余额不足，当前余额：".($botuser->cash_usdt + 0)." USDT，需要：".$kouPrice." USDT，请先在机器人充值USDT！";
                            $response = $telegram->sendMessage([
                                'chat_id' => $chatid, 
                                'text' => $replytext, 
                                'reply_to_message_id' => $result['message']['message_id'],
                                'parse_mode' => 'MarkDown',
                                'allow_sending_without_reply' => true
                            ]);
                            return '';
                        }else{
                            TelegramBotUser::where('rid',$botuser->rid)->update(['cash_usdt' => $botuser->cash_usdt - $kouPrice]);
                        }
                    }
                }
                
                //如果存在则增加次数
                if($biWallet){
                    $oldCishu = $biWallet->max_buy_quantity;
                    $res = EnergyAiBishu::where('rid',$biWallet->rid)->update(['max_buy_quantity' => $biWallet->max_buy_quantity + $message[1],'update_time' => nowDate()]);
                }else{
                    $oldCishu = 0;
                    $res = EnergyAiBishu::create([
                        'bot_rid' => $bot_rid,
                        'wallet_addr' => $message[0],
                        'tg_uid' => '',
                        'total_buy_usdt' => 0,
                        'max_buy_quantity' => $message[1],
                        'total_buy_quantity' => 0,
                        'back_comments' => '',
                        'create_time' => nowDate()
                    ]);
                }
                
                $replytext = "地址：".$message[0].($res ? " 添加成功" : " 添加失败")."\n"
                            ."原购买总次数：".$oldCishu."\n"
                            ."本次新增次数：".$message[1];
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                
                return '';
                
            //笔数地址绑定通知
            }elseif(in_array(mb_substr($message,0,4),['绑定笔数','笔数绑定'])){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令只能管理员使用且私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $message = trim(str_replace(['绑定笔数','笔数绑定'],'',$message));
                
                $message = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);
                
                if(!isset($message[0]) || !isset($message[1]) || (isset($message[0]) && mb_substr($message[0],0,1) != 'T' && mb_strlen($message[0]) != 34)){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => "⚠️请输入格式：绑定笔数 Tdfal3432xxxxxx  687954545 ，表示给笔数地址绑定用户687954545通知！\n如果地址需要绑定多个用户通知，用英文逗号,隔开", 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $biWallet = EnergyAiBishu::where('wallet_addr',$message[0])->where('bot_rid',$bot_rid)->first();
                //更改通知
                if(isset($biWallet)){
                    //校验仅管理员可执行命令
                    $adminwallet = EnergyPlatformBot::where('bot_rid', $bot_rid)->where('status',0)->first();
                    
                    if(empty($adminwallet)){
                        $replytext = '⚠️机器人未配置能量平台或者未启用,仅允许管理员执行,请检查数据';
                    }
                    
                    $adminarr = explode(',', $adminwallet->tg_admin_uid);
                    
                    if(empty($adminwallet->tg_admin_uid)){
                        $replytext = '⚠️能量平台管理员不正确,仅允许管理员执行,请检查数据1';
                    }elseif(!in_array($chatid,$adminarr)){
                        $replytext = '⚠️能量平台管理员不正确,仅允许管理员执行,请检查数据2';
                    }else{
                        EnergyAiBishu::where('wallet_addr',$message[0])->where('bot_rid',$bot_rid)->update(['tg_uid' => $message[1]]);
                        $replytext = '✅笔数地址：'.$message[0].' 通知已更改！';
                    };
                    
                }else{
                    $replytext = "❌地址：".$message[0]."在该机器人中不存在笔数套餐，请先增加笔数";
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                
                return '';
                
            //根据助记词或者私钥查询波场地址和余额
            }elseif(mb_substr($message,0,3) == '查私钥' || mb_substr($message,0,4) == '查助记词' || mb_substr($message,0,3) == '查地址'){
                $message = trim(str_replace(['查私钥','查助记词','查地址'],'',$message)); 
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => '❌查询中'.$message.'，请耐心等待！请勿重复查询！', 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                
                $replytext = $this->querytronmnepri($message);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                
                return '';
            
            //授权波场
            }elseif(mb_substr($message,0,4) == '授权波场'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令为管理员命令且只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $message = trim(str_replace(['授权波场'],'',$message)); 
                $message = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);
                if(isset($message[0]) && mb_substr($message[0],0,1) != 'T' && mb_strlen($message[0]) != 34){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '请输入需要授权的波场钱包地址！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                if(isset($message[0])){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '❌授权中 '.$message[0].'，请耐心等待！请勿重复执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    
                    $replytext = $this->approvetrc($message);
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                }
                return '';
                
            //多签波场
            }elseif(mb_substr($message,0,4) == '多签波场'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令为管理员命令且只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $message = trim(str_replace(['多签波场'],'',$message)); 
                $message = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);
                if(isset($message[0]) && mb_substr($message[0],0,1) != 'T' && mb_strlen($message[0]) != 34){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '请输入需要多签的波场钱包地址！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                if(isset($message[0])){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '❌多签中 '.$message[0].'，请耐心等待！请勿重复执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    
                    $replytext = $this->multitrc($message);
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                }
                return '';
                
            //根据助记词或者私钥查询erc地址和余额
            }elseif(mb_substr($message,0,5) == '查以太私钥' || mb_substr($message,0,6) == '查以太助记词' || mb_substr($message,0,5) == '查以太地址'){
                $message = trim(str_replace(['查以太私钥','查以太助记词','查以太地址'],'',$message)); 
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => '❌查询中'.$message.'，请耐心等待！请勿重复查询！', 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                
                $replytext = $this->queryercmnepri($message);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);

                return '';
                
            //管理员可用命令:预支给xxxxx10
            }elseif(mb_substr($message,0,3) == '预支给'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令为管理员命令且只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $message = trim(str_replace(['预支给'],'',$message)); 
                $arr = explode(" ",$message); //空格分割字符串
                
                $message = 0; //地址 
                $message2 = 15; //trx数量,默认最大预支15个,改为0就表示要必填
                
                if(!empty($arr)){
                    if(!empty($arr[0])){
                        $message = $arr[0]; //地址 
                    }
                    if(!empty($arr[1])){
                        $message2 = $arr[1]; //trx数量
                    }
                }
                
                if(mb_substr($message,0,1) == 'T' && mb_strlen($message) == 34 && $message2 != 0){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '❌预支中：'.$message.'，请耐心等待！请勿重复预支！！！！重复提交会导致多次预支！！！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    
                    $replytext = $this->adminyuzhi($message,$bot_rid,$chatid,$message2);
                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }else{
                    $replytext = '格式错误，请输入格式：预支给xxxxxxx 15'.PHP_EOL.PHP_EOL
                                .'xxxxxxx是钱包地址，15是trx数量，如果不输入后面的数量，则默认预支15个trx'.PHP_EOL
                                .'比如  预支给TYASr5UV6HEcXatwdFQfmLVUqQQQMUxHLS 15';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
            //管理员可用命令:激活地址xxxxx
            }elseif(mb_substr($message,0,4) == '激活地址'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令为管理员命令且只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $message = trim(str_replace(['激活地址'],'',$message)); 
                
                if(mb_substr($message,0,1) == 'T' && mb_strlen($message) == 34){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '❌激活中：'.$message.'，请耐心等待！请勿重复激活！！！！重复提交会导致多次转账激活！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    
                    $replytext = $this->adminactive($message,$bot_rid,$chatid);
                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                }else{
                    $replytext = '格式错误，请输入格式：激活地址xxxxxxx'.PHP_EOL.PHP_EOL
                                .'xxxxxxx是钱包地址'.PHP_EOL
                                .'比如  激活地址TYASr5UV6HEcXatwdFQfmL';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                }
                return '';
                
            //自助预支TRX
            }elseif(mb_substr($message,0,2) == '预支'){
                $message = trim(str_replace(['预支'],'',$message)); 
                //内联按钮
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                        ]
                    ]
                ];
                $encodedKeyboard = json_encode($keyboard);
                
                if(mb_substr($message,0,1) == 'T' && mb_strlen($message) == 34){
                    $replytext = $this->tronyuzhi($message,$bot_rid);
                    if(isset($result['message']['message_id'])){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                    }else{
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                    }
                    
                    return '';
                }else{
                    $replytext = '输入格式错误，请输入格式：预支xxxxxxx'.PHP_EOL
                                .'➖➖➖➖➖➖➖➖➖➖'.PHP_EOL
                                .'比如  预支TYASr5UV6HEcXatwdFQfmL'.PHP_EOL.PHP_EOL
                                .'如需紧急预支请联系客服人工预支';
                                
                    if(isset($result['message']['message_id'])){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                    }else{
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                    }
                    
                    return '';
                }
                
            //统计-闪兑
            }elseif($message == '统计'){
                $statres = DB::select("SELECT transferto_address,sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') then amount else 0 end) as today_total, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') then amount else 0 end) as month_total, sum(amount) as total, sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') then sendback_amount else 0 end) as today_back_total, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') then sendback_amount else 0 end) as month_back_total, sum(sendback_amount) as back_total, count(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') then 1 else null end) as today_count, count(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') then 1 else null end) as month_count, count(1) as total_count,sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 then amount else 0 end) as today_total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and process_status = 9 then amount else 0 end) as month_total_success, sum(case when process_status = 9 then amount else 0 end) as total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 then sendback_amount else 0 end) as today_back_total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and process_status = 9 then sendback_amount else 0 end) as month_back_total_success, sum(case when process_status = 9 then sendback_amount else 0 end) as back_total_success, count(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 then 1 else null end) as today_count_success, count(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and process_status = 9 then 1 else null end) as month_count_success, count(case when process_status = 9 then 1 else null end) as total_count_success FROM t_transit_wallet_trade_list group by transferto_address");
                if(empty($statres)){
                    $replytext = '闪兑订单交易无数据';
                }else{
                    $replytext = "💹闪兑统计\n\n";
                    foreach ($statres as $k => $v) {
                        $replytext = $replytext."钱包地址：<code>".$v->transferto_address."</code>\n"
                                ."当天笔数：".$v->today_count." 笔 (成功：".$v->today_count_success." 笔)\n"
                                ."当天进USDT：".$v->today_total." USDT (成功：".$v->today_total_success." USDT)\n"
                                ."当天出TRX：".$v->today_back_total." TRX (成功：".$v->today_back_total_success." TRX)\n\n"
                                ."当月笔数：".$v->month_count." 笔 (成功：".$v->month_count_success." 笔)\n"
                                ."当月进USDT：".$v->month_total." USDT (成功：".$v->month_total_success." USDT)\n"
                                ."当月出TRX：".$v->month_back_total." TRX (成功：".$v->month_back_total_success." TRX)\n\n"
                                ."总笔数：".$v->total_count." 笔 (成功：".$v->total_count_success." 笔)\n"
                                ."总进USDT：".$v->total." USDT (成功：".$v->total_success." USDT)\n"
                                ."总出TRX：".$v->back_total." TRX (成功：".$v->back_total_success." TRX)\n\n";
                    }
                    //取所有总数据
                    $statresTotal = DB::select("SELECT sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') then amount else 0 end) as today_total, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') then amount else 0 end) as month_total, sum(amount) as total, sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') then sendback_amount else 0 end) as today_back_total, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') then sendback_amount else 0 end) as month_back_total, sum(sendback_amount) as back_total, count(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') then 1 else null end) as today_count, count(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') then 1 else null end) as month_count, count(1) as total_count,sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 then amount else 0 end) as today_total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and process_status = 9 then amount else 0 end) as month_total_success, sum(case when process_status = 9 then amount else 0 end) as total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 then sendback_amount else 0 end) as today_back_total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and process_status = 9 then sendback_amount else 0 end) as month_back_total_success, sum(case when process_status = 9 then sendback_amount else 0 end) as back_total_success, count(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 then 1 else null end) as today_count_success, count(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and process_status = 9 then 1 else null end) as month_count_success, count(case when process_status = 9 then 1 else null end) as total_count_success FROM t_transit_wallet_trade_list");
                    if(!empty($statresTotal)){
                        $total = "当天笔数：".$statresTotal[0]->today_count." 笔 (成功：".$statresTotal[0]->today_count_success." 笔)\n"
                                ."当天进USDT：".$statresTotal[0]->today_total." USDT (成功：".$statresTotal[0]->today_total_success." USDT)\n"
                                ."当天出TRX：".$statresTotal[0]->today_back_total." TRX (成功：".$statresTotal[0]->today_back_total_success." TRX)\n\n"
                                ."当月笔数：".$statresTotal[0]->month_count." 笔 (成功：".$statresTotal[0]->month_count_success." 笔)\n"
                                ."当月进USDT：".$statresTotal[0]->month_total." USDT (成功：".$statresTotal[0]->month_total_success." USDT)\n"
                                ."当月出TRX：".$statresTotal[0]->month_back_total." TRX (成功：".$statresTotal[0]->month_back_total_success." TRX)\n\n"
                                ."总笔数：".$statresTotal[0]->total_count." 笔 (成功：".$statresTotal[0]->total_count_success." 笔)\n"
                                ."总进USDT：".$statresTotal[0]->total." USDT (成功：".$statresTotal[0]->total_success." USDT)\n"
                                ."总出TRX：".$statresTotal[0]->back_total." TRX (成功：".$statresTotal[0]->back_total_success." TRX)\n\n";
                        $replytext = $replytext."<b>所有钱包总数据</b>\n".$total;
                    }
                }
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true
                ]);
                return '';
            
            //统计-能量
            }elseif(in_array($message,['能量统计','统计能量'])){
                $statres = DB::select("SELECT transferto_address,sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and coin_name = 'trx' then amount else 0 end) as today_trx_total,sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and coin_name = 'usdt' then amount else 0 end) as today_usdt_total, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and coin_name = 'trx' then amount else 0 end) as month_trx_total, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and coin_name = 'usdt' then amount else 0 end) as month_usdt_total,sum(case when coin_name = 'trx' then amount else 0 end) as trx_total,sum(case when coin_name = 'usdt' then amount else 0 end) as usdt_total,sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 and coin_name = 'trx' then amount else 0 end) as today_trx_total_success,sum(case when DATE_FORMAT(get_time,'%Y-%m-%d') = DATE_FORMAT(sysdate(),'%Y-%m-%d') and process_status = 9 and coin_name = 'usdt' then amount else 0 end) as today_usdt_total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and coin_name = 'trx' and process_status = 9 then amount else 0 end) as month_trx_total_success, sum(case when DATE_FORMAT(get_time,'%Y-%m') = DATE_FORMAT(sysdate(),'%Y-%m') and coin_name = 'usdt' and process_status = 9 then amount else 0 end) as month_usdt_total_success,sum(case when coin_name = 'trx' and process_status = 9 then amount else 0 end) as trx_total_success,sum(case when coin_name = 'usdt' and process_status = 9 then amount else 0 end) as usdt_total_success FROM t_energy_wallet_trade_list group by transferto_address");
                if(empty($statres)){
                    $replytext = '能量订单交易无数据';
                }else{
                    $replytext = "🔋能量统计\n\n";
                    $total_today_trx_total = 0;
                    $total_today_usdt_total = 0;
                    $total_today_trx_total_success = 0;
                    $total_today_usdt_total_success = 0;
                    $total_month_trx_total = 0;
                    $total_month_usdt_total = 0;
                    $total_month_trx_total_success = 0;
                    $total_month_usdt_total_success = 0;
                    $total_trx_total = 0;
                    $total_usdt_total = 0;
                    $total_trx_total_success = 0;
                    $total_usdt_total_success = 0;
                    
                    foreach ($statres as $k => $v) {
                        $replytext = $replytext."钱包地址：<code>".$v->transferto_address."</code>\n"
                                    ."今日：". $v->today_trx_total ." TRX. " . $v->today_usdt_total . " USDT (成功：". $v->today_trx_total_success ." TRX. " . $v->today_usdt_total_success . " USDT)\n"
                                    ."当月：". $v->month_trx_total ." TRX. " . $v->month_usdt_total . " USDT (成功：". $v->month_trx_total_success ." TRX. " . $v->month_usdt_total_success . " USDT)\n"
                                    ."总数据：". $v->trx_total ." TRX. " . $v->usdt_total . " USDT (成功：". $v->trx_total_success ." TRX. " . $v->usdt_total_success . " USDT)\n\n";
                        
                        $total_today_trx_total = $total_today_trx_total + $v->today_trx_total;
                        $total_today_usdt_total = $total_today_usdt_total + $v->today_usdt_total;
                        $total_today_trx_total_success = $total_today_trx_total_success + $v->today_trx_total_success;
                        $total_today_usdt_total_success = $total_today_usdt_total_success + $v->today_usdt_total_success;
                        $total_month_trx_total = $total_month_trx_total + $v->month_trx_total;
                        $total_month_usdt_total = $total_month_usdt_total + $v->month_usdt_total;
                        $total_month_trx_total_success = $total_month_trx_total_success + $v->month_trx_total_success;
                        $total_month_usdt_total_success = $total_month_usdt_total_success + $v->month_usdt_total_success;
                        $total_trx_total = $total_trx_total + $v->trx_total;
                        $total_usdt_total = $total_usdt_total + $v->usdt_total;
                        $total_trx_total_success = $total_trx_total_success + $v->trx_total_success;
                        $total_usdt_total_success = $total_usdt_total_success + $v->usdt_total_success;
                    }
                    
                    $total = "今日：". $total_today_trx_total ." TRX. " . $total_today_usdt_total . " USDT (成功：". $total_today_trx_total_success ." TRX. " . $total_today_usdt_total_success . " USDT)\n"
                            ."当月：". $total_month_trx_total ." TRX. " . $total_month_usdt_total . " USDT (成功：". $total_month_trx_total_success ." TRX. " . $total_month_usdt_total_success . " USDT)\n"
                            ."总数据：". $total_trx_total ." TRX. " . $total_usdt_total . " USDT (成功：". $total_trx_total_success ." TRX. " . $total_usdt_total_success . " USDT)\n\n";
                    $replytext = $replytext."<b>所有钱包总数据</b>\n".$total;
                }
                            
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true
                ]);
                return '';
                
            //管理员可用命令:能量给xxx 65000 0
            }elseif(mb_substr($message,0,3) == '能量给' || mb_substr($message,0,5) == '能量强制给'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令为管理员命令且只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                $isQiangzhi = mb_substr($message,0,5) == '能量强制给' ?'Y':'N';
                
                $message = trim(str_replace(['能量给','能量强制给'],'',$message)); 
                $message = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);
                
                if(!isset($message) || empty($message)){
                    $replytext = '输入格式错误，请输入格式：能量给xxxxxxx  xxxxxxx为钱包地址。'.PHP_EOL.PHP_EOL
                                .'比如  能量给TYASr5UV6HEcXatwdFQfmL';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                if(mb_substr($message[0],0,1) == 'T' && mb_strlen($message[0]) == 34){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量下发中，请勿重复发送命令，请稍后！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    
                    $replytext = $this->dailienergy($message,$bot_rid,$chatid,$isQiangzhi);
                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext ?? '未知错误', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true
                    ]);
                }else{
                    $replytext = '输入格式错误，请输入格式：能量给xxxxxxx  xxxxxxx为钱包地址。'.PHP_EOL.PHP_EOL
                                .'比如  能量给TYASr5UV6HEcXatwdFQfmLVUqQQQMUxHLS';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                }
                return '';
                
            //管理员可用命令:下发trx,下发usdt
            }elseif(mb_substr($message,0,5) == '下发trx' || mb_substr($message,0,5) == '下发TRX' || mb_substr($message,0,6) == '下发usdt' || mb_substr($message,0,6) == '下发USDT'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令为管理员命令且只能私聊机器人执行！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                if(mb_substr($message,0,5) == '下发trx' || mb_substr($message,0,5) == '下发TRX' ){
                    $sendType = 'trx';
                }else{
                    $sendType = 'usdt';
                }

                $message = trim(str_replace(['下发trx','下发TRX','下发usdt','下发USDT'],'',$message)); 
                $message = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);
                
                if(!isset($message) || empty($message)){
                    $replytext = '输入格式错误，请输入格式：下发trx xxxxxxx 100  xxxxxxx为钱包地址。'.PHP_EOL.PHP_EOL
                                .'比如  下发trx TYASr5UV6HEcXatwdFQfmL 100';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //增加限制1分钟才能执行一次
                #设置缓存
                $account_xiafa = getRedis('adminsend'.$bot_rid);
                if(!empty($account_xiafa)){
                    $replytext = '该命令限制为30秒才能下发一次';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                setexRedis('adminsend'.$bot_rid,$bot_rid,30);
                
                if(mb_substr($message[0],0,1) == 'T' && mb_strlen($message[0]) == 34 && isset($message[1])){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '下发中，请勿重复发送命令，请稍后！', 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    
                    $replytext = $this->adminsend($message[0],$bot_rid,$chatid,$sendType,$message[1]);
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '波场链上查询', 'url' => 'https://tronscan.org/#/address/'.$message[0]],
                                ['text' => '查地址授权', 'callback_data' => '查授权'.$message[0]]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }else{
                    $replytext = '输入格式错误，请输入格式：下发trx xxxxxxx 100  xxxxxxx为钱包地址。'.PHP_EOL.PHP_EOL
                                .'比如  下发trx TYASr5UV6HEcXatwdFQfmL 100';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                }
                return '';
                
            //出售能量的命令
            }elseif(mb_substr($message,0,7) == 'energy_' && mb_strlen($message) == 39 && $inlinecall == 'Y'){
                $username = $request->callback_query['from']['username'] ?? '';
                
                $packageData = EnergyPlatformPackage::from('t_energy_platform_package as a')
                            ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                            ->where('a.callback_data', $message)
                            ->where('a.status',0)
                            ->where('b.status',0)
                            ->select('a.rid','a.package_name','a.energy_amount','a.trx_price','a.show_notes','b.receive_wallet','b.is_open_ai_trusteeship')
                            ->first();
                
                if(empty($packageData)){
                    $replytext = "@".$username." <b>您好！该套餐已暂停购买，请选择其他套餐！</b>\n";
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy']
                            ]
                        ]
                    ];
                    
                    $reply_markup = json_encode($keyboard);
                }else{
                    $replytext = "@".$username." <b>您好！请仔细核对支付金额和支付地址</b>\n\n"
                            .$packageData->show_notes."\n"
                            ."➖➖➖➖➖➖➖➖\n"
                            ."<b>🟢当前套餐：".$packageData->package_name."</b>\n"
                            ."<b>🟢套餐能量：</b>".$packageData->energy_amount." (24小时恢复满)\n"
                            ."<b>🟢支付金额：</b><code>".$packageData->trx_price."</code> <b>TRX</b> (点击金额复制)\n"
                            ."<b>🟢支付地址：</b><code>".$packageData->receive_wallet."</code>\n"
                            ."➖➖➖➖➖➖➖➖\n"
                            ."⚠️付款成功后,能量将自动发货到付款地址\n"
                            ."⚠️<b>点击地址复制，点击金额复制，金额或者地址错误将无法追回！</b>";
                    
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => 'TRX 余额购买', 'callback_data' => 'balancebuytrx'.$packageData->rid],
                                ['text' => 'USDT 余额购买', 'callback_data' => 'balancebuyusd'.$packageData->rid]
                            ],
                            [
                                ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy']
                            ]
                        ]
                    ];
                    
                    $reply_markup = json_encode($keyboard);
                };
                
                // #查询能量放入
                // $keyboardList = EnergyPlatformPackage::from('t_energy_platform_package as a')
                //             ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                //             ->where('a.bot_rid', $bot_rid)
                //             ->where('a.status', 0)
                //             ->where('b.status', 0)
                //             ->select('a.package_name as keyboard_name','a.callback_data as keyboard_value')
                //             ->orderBy('a.seq_sn','desc')
                //             ->get();
                            
                // //有键盘的时候显示
                // if($keyboardList->count() > 0){
                //     $keyboardone = [];
                //     $keyboard = [];
                //     $s = 0;
                    
                //     $keyboardone['text'] = "点击 ↓ 重新选择套餐(点我查看说明)";
                //     $keyboardone['callback_data'] = "重新选择套餐energy";
                //     $keyboard[0][] = $keyboardone;
                //     $keyboardone = [];
                //     $diyihang = 'Y';
                    
                //     foreach ($keyboardList as $k => $v) {
                //         //内联按钮
                //         $keyboardone['text'] = $v->keyboard_name;
                //         $keyboardone['callback_data'] = $v->keyboard_value;
                        
                //         if(!empty($keyboard)){
                //             if(count($keyboard[$s]) == 2 || $diyihang == 'Y'){
                //                 $s++;
                //                 $diyihang = 'N';
                //             }
                //         }
                        
                //         $keyboard[$s][] = $keyboardone;
                //         $keyboardone = [];
                //     }
                    
                //     $isputaitrusteeship = 'N';
                //     //放入智能托管按钮
                //     if($packageData->is_open_ai_trusteeship == 'Y'){
                //         //如果是群聊,则放入机器人地址
                //         if(mb_substr($chatid,0,1) == '-'){
                //             //内联按钮
                //             $keyboardone['text'] = '❇️智能托管';
                //             $keyboardone['url'] = 'https://t.me/'.$data->bot_username;
                //         }else{
                //             //内联按钮
                //             $keyboardone['text'] = '❇️智能托管';
                //             $keyboardone['callback_data'] = 'aitrusteeship';
                //         }
                //         $s++;
                //         $keyboard[$s][] = $keyboardone;
                        
                //         $isputaitrusteeship = 'Y';
                //     }
                    
                //     if($packageData->is_open_bishu == 'Y'){
                //         //如果是群聊,则放入机器人地址
                //         // if(mb_substr($chatid,0,1) == '-'){
                //         //     //内联按钮
                //         //     $keyboardone['text'] = '🖌笔数套餐';
                //         //     $keyboardone['url'] = 'https://t.me/'.$data->bot_username;
                //         // }else{
                //             //内联按钮
                //             $keyboardone = [];
                //             $keyboardone['text'] = '🖌笔数套餐';
                //             $keyboardone['callback_data'] = 'energybishu';
                //         // }
                //         if($isputaitrusteeship == 'N'){
                //             $s = $s == 0?0:($s + 1);
                //         }
                        
                //         $keyboard[$s][] = $keyboardone;
                //     }
                    
                //     $reply_markup = [
                //         'inline_keyboard' => $keyboard
                //     ];
                //     $reply_markup = json_encode($reply_markup);
                // //没有套餐的时候隐藏键盘
                // }else{
                //     $replytext = $replytext."😭😭😭<b>暂无可用能量套餐，请联系管理员！</b>";
                    
                //     $reply_markup = $telegram->replyKeyboardHide([
                //         'keyboard' => [], 
                //         'resize_keyboard' => true,  //设置为true键盘不会那么高
                //         'one_time_keyboard' => false
                //     ]);
                // }
                
                #发送图片
                if(!empty($packageData->package_pic)){
                    $response = $telegram->sendPhoto([
                        'chat_id' => $chatid, 
                        'photo' => InputFile::create($packageData->package_pic, 'demo'),
                        'caption' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                }else{
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                }
                return '';
            
            //购买能量-使用余额支付
            }elseif(in_array(mb_substr($message,0,13),['balancebuytrx','balancebuyusd']) && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //用户是否绑定了地址
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行快捷购买能量";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $reply_markup = json_encode($keyboard);
                }else{
                    if(empty($botuser->bind_trc_wallet_addr)){
                        $replytext = "🔴<b>请点击下方按钮，先绑定您的钱包地址</b>";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '绑定波场钱包', 'callback_data' => 'userbindaddress'],
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $reply_markup = json_encode($keyboard);
                    }else{
                        $rid = str_replace(['balancebuytrx','balancebuyusd'],'',$message);
                        $packageData = EnergyPlatformPackage::from('t_energy_platform_package as a')
                            ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                            ->where('a.rid', $rid)
                            ->where('a.status',0)
                            ->where('b.status',0)
                            ->select('a.rid','a.package_name','a.energy_amount','a.trx_price','a.usdt_price')
                            ->first();
                        if(empty($packageData)){
                            $replytext = "<b>您好！该套餐已暂停购买，请选择其他套餐！</b>\n";
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy']
                                    ]
                                ]
                            ];
                            
                            $reply_markup = json_encode($keyboard);
                        }else{
                            $paybalancetype = mb_substr($message,0,13) == 'balancebuytrx' ?($packageData->trx_price." TRX"):($packageData->usdt_price." USDT");
                            $currentpay = mb_substr($message,0,13) == 'balancebuytrx' ?'trx':'usd';
                            $otherpay = mb_substr($message,0,13) == 'balancebuytrx' ?'balancebuyusd':'balancebuytrx';
                            $otherpaytext = mb_substr($message,0,13) == 'balancebuytrx' ?'↔️USDT 余额购买':'↔️TRX 余额购买';
                            $replytext = "🟢<b>购买地址：</b><code>".$botuser->bind_trc_wallet_addr."</code>\n"
                                        ."购买套餐：".$packageData->package_name."\n"
                                        ."购买能量：".$packageData->energy_amount."\n"
                                        ."支付方式：".$paybalancetype."\n\n"
                                        ."🔶如果给其他地址购买能量，请点击下方按钮《给其他地址购买》";
                            //内联按钮
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '☑️确定购买', 'callback_data' => 'balancebuyconf'.$currentpay.$rid]
                                    ],
                                    [
                                        ['text' => $otherpaytext, 'callback_data' => $otherpay.$rid],
                                        ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy']
                                    ],
                                    [
                                        ['text' => '❓地址不对? 更换绑定钱包', 'callback_data' => 'userbindaddress'],
                                        ['text' => '🟣给其他地址购买', 'callback_data' => 'balancebuyother'.$rid.'_'.$currentpay]
                                    ]
                                ]
                            ];
                            $reply_markup = json_encode($keyboard);
                        }
                    }
                }

                $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                return '';
            
            //购买能量-使用余额支付-确认购买
            }elseif(in_array(mb_substr($message,0,14),['balancebuyconf']) && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $reply_markup = json_encode($keyboard);
                }else{
                    $paytype = mb_substr($message,14,3);
                    $rid = str_replace(['balancebuyconftrx','balancebuyconfusd'],'',$message);
                    $packageData = EnergyPlatformPackage::from('t_energy_platform_package as a')
                            ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                            ->where('a.rid', $rid)
                            ->where('a.status',0)
                            ->where('b.status',0)
                            ->select('a.rid','a.package_name','a.energy_amount','a.trx_price','a.usdt_price','a.energy_day')
                            ->first();
                            
                    if(empty($packageData)){
                        $replytext = "<b>您好！该套餐已暂停购买，请选择其他套餐！</b>\n";
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy']
                                ]
                            ]
                        ];
                        
                        $reply_markup = json_encode($keyboard);
                    }else{
                        if($paytype == 'trx' && floatval($botuser->cash_trx) >= floatval($packageData->trx_price)){
                            TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->update(['cash_trx' => $botuser->cash_trx - $packageData->trx_price]);
                            EnergyQuickOrder::insert([
                                'bot_rid' => $bot_rid,    
                                'tg_uid' => $chatid,    
                                'wallet_addr' => $botuser->bind_trc_wallet_addr,    
                                'energy_amount' => $packageData->energy_amount,    
                                'energy_day' => $packageData->energy_day,  
                                'package_name' => $packageData->package_name,
                                'package_rid' => $packageData->rid,
                                'status' => 1,    
                                'pay_price' => $packageData->trx_price,
                                'pay_type' => 'trx',    
                                'pay_time' => nowDate()
                            ]);
                            
                            $replytext ="✅恭喜您，购买成功！能量将在5秒内到账\n" 
                                        ."购买地址：<code>".$botuser->bind_trc_wallet_addr."</code>\n"
                                        ."购买套餐：".$packageData->package_name."\n"
                                        ."购买能量：".$packageData->energy_amount."\n"
                                        ."支付方式：".$packageData->trx_price." TRX\n\n"
                                        ."剩余可用余额：".($botuser->cash_trx - $packageData->trx_price)." TRX ".$botuser->cash_usdt." USDT";
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '🔄重新购买套餐', 'callback_data' => '/buyenergy']
                                    ]
                                ]
                            ];
                            
                            $reply_markup = json_encode($keyboard);
                        }elseif($paytype == 'usd' && floatval($botuser->cash_usdt) >= floatval($packageData->usdt_price)){
                            TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->update(['cash_usdt' => $botuser->cash_usdt - $packageData->usdt_price]);
                            EnergyQuickOrder::insert([
                                'bot_rid' => $bot_rid,    
                                'tg_uid' => $chatid,    
                                'wallet_addr' => $botuser->bind_trc_wallet_addr,    
                                'energy_amount' => $packageData->energy_amount,    
                                'energy_day' => $packageData->energy_day,  
                                'package_name' => $packageData->package_name,
                                'package_rid' => $packageData->rid,
                                'status' => 1,    
                                'pay_price' => $packageData->usdt_price,
                                'pay_type' => 'usdt',    
                                'pay_time' => nowDate()
                            ]);
   
                            $replytext ="✅恭喜您，购买成功！能量将在5秒内到账\n" 
                                        ."购买地址：<code>".$botuser->bind_trc_wallet_addr."</code>\n"
                                        ."购买套餐：".$packageData->package_name."\n"
                                        ."购买能量：".$packageData->energy_amount."\n"
                                        ."支付方式：".$packageData->usdt_price." USDT\n\n"
                                        ."剩余可用余额：".$botuser->cash_trx." TRX ".($botuser->cash_usdt - $packageData->usdt_price)." USDT";
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '🔄重新购买套餐', 'callback_data' => '/buyenergy']
                                    ]
                                ]
                            ];
                            
                            $reply_markup = json_encode($keyboard);
                        }else{
                            $replytext = "<b>❌余额不足，请及时充值！</b>\n"
                                        ."TRX 余额：".$botuser->cash_trx."\n"
                                        ."USDT 余额：".$botuser->cash_usdt."\n\n"
                                        ."当前套餐需要：".$packageData->trx_price." TRX 或者：".$packageData->usdt_price." USDT";
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy'],
                                        ['text' => '💵充值余额', 'callback_data' => 'aitrusteeshiprechargetrx']
                                    ]
                                ]
                            ];
                            
                            $reply_markup = json_encode($keyboard);
                        }
                    }
                }
                
                $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                return '';
                
            //绑定波场钱包
            }elseif($message == 'userbindaddress' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $replytext = "<b>请在2分钟内回复此消息，直接回复您要绑定的波场地址</b>\n\n"
                            ."点击 /start 重新开始";
                
                $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要绑定的波场地址"]);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('userbindaddressconfirm'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('userbindaddressconfirm'.$chatid);
                }
                setexRedis('userbindaddressconfirm'.$chatid,$response['message_id'],120);
                
                return '';
            
            //购买能量闪租-给其他地址购买
            }elseif(in_array(mb_substr($message,0,15),['balancebuyother']) && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该命令请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                $message = str_replace(['balancebuyother'],'',$message);
                $explodeArr = explode("_", $message);
                $rid = implode("_", array_slice($explodeArr, 0, 1));
                $paytype = implode("_", array_slice($explodeArr, 1));
                
                $packageData = EnergyPlatformPackage::from('t_energy_platform_package as a')
                            ->join('t_energy_platform_bot as b','a.bot_rid','b.bot_rid')
                            ->where('a.rid', $rid)
                            ->where('a.status',0)
                            ->where('b.status',0)
                            ->select('a.rid','a.package_name','a.energy_amount','a.trx_price','a.usdt_price','a.show_notes')
                            ->first();
                if(empty($packageData)){
                    $replytext = "<b>您好！该套餐已暂停购买，请选择其他套餐！</b>\n";
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔄重新选择套餐', 'callback_data' => '/buyenergy']
                            ]
                        ]
                    ];
                    
                    $encodedKeyboard = json_encode($keyboard);
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    return '';
                    
                }else{
                    $paybalancetype = $paytype == 'trx' ?($packageData->trx_price." TRX"):($packageData->usdt_price." USDT");
                    $replytext = "<b>请在2分钟内回复此消息，直接回复您要购买能量的波场钱包地址</b>\n\n"
                                ."购买套餐：".$packageData->package_name."\n"
                                ."购买能量：".$packageData->energy_amount."\n"
                                ."支付方式：".$paybalancetype." 余额支付，请确保 ".strtoupper($paytype)." 余额充足！\n\n"
                                ."点击 /start 重新开始";
        
                    $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要购买能量的波场钱包地址，该地址一定要是激活后的钱包地址"]);
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    
                    #设置缓存
                    $add_ai_address = getRedis('balancebuyotherconfirm'.$chatid);
                    if(!empty($add_ai_address)){
                        deleteRedis('balancebuyotherconfirm'.$chatid);
                    }
                    setexRedis('balancebuyotherconfirm'.$chatid,$response['message_id']."_".$rid."_".$paytype,120);
                    
                    return '';
                }
                
            //点击能量的重新选择套餐
            }elseif($message == '重新选择套餐energy' && $inlinecall == 'Y'){
                $replytext = "波场手续费（⚠️务必仔细阅读⚠️）\n"
                            ."🔴对方有U，消耗约3.2万，燃烧13.44TRX\n"
                            ."🔴对方无U，消耗约6.5万，燃烧27.30TRX\n"
                            ."➖➖➖转账还需少量带宽➖➖➖\n"
                            ."🟢通过租赁能量，节省一大笔TRX\n"
                            ."🟢可选择租赁次数，租赁时长，能量24小时会恢复";
                            
                //调用官方方法
                $param = [
                    'callback_query_id' => $request->callback_query['id'], 
                    'text' => $replytext,
                    'show_alert' => true
                ];
                
                $urlString = "https://api.telegram.org/bot".$data->bot_token."/answerCallbackQuery";
                
                $response = post_multi($urlString,$param);
                return '';
                
            //点击能量的智能托管
            }elseif($message == 'aitrusteeship' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $platformBot = EnergyPlatformBot::where("bot_rid",$bot_rid)->first();
                    if(isset($platformBot->trx_price_energy_32000) && isset($platformBot->trx_price_energy_65000)){
                        $replytext = "<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."⚠️TRX余额不足时，不再执行智能托管，请及时充值\n"
                            ."⚠️自动监控托管地址的能量，不足时自动补足能量\n"
                            ."⚠️充值的USDT可点击下方转换为TRX\n\n"
                            ."<b>托管单价</b>：65000能量 <u>".$platformBot->trx_price_energy_32000." TRX</u>，131000能量 <u>".$platformBot->trx_price_energy_65000." TRX</u>\n"
                            ."<b>请保证余额充足,点击下方可充值余额！</b>";
                    }else{
                        $replytext = "<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."⚠️TRX余额不足时，不再执行智能托管，请及时充值\n"
                            ."⚠️自动监控托管地址的能量，不足时自动补足能量\n"
                            ."⚠️充值的USDT可点击下方转换为TRX\n\n"
                            ."<b>机器人未设置智能托管价格，请联系客服</b>\n"
                            ."<b>请保证余额充足,点击下方可充值余额！</b>";
                    }
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔥TRX充值', 'callback_data' => 'aitrusteeshiprechargetrx'],
                                ['text' => '🔥USDT充值', 'callback_data' => 'aitrusteeshiprechargeusdt'],
                                ['text' => '🔀U转TRX', 'callback_data' => 'aitrusteeshipusdtswaptrx']
                            ],
                            [
                                ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                            ],
                            [
                                ['text' => '👑我的托管地址', 'callback_data' => 'aitrusteeshipmyaddress']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
            
            //点击能量的笔数套餐
            }elseif($message == 'energybishu' && $inlinecall == 'Y'){
                $platformBot = EnergyPlatformBot::where("bot_rid",$bot_rid)->first();
                $keyreply = TelegramBotKeyreply::where('bot_rid', $bot_rid)->where('status',0)->where('opt_type',10)->first();
                $replytext = ($keyreply->reply_content == '--' ?'':$keyreply->reply_content)."\n\n👉<b>每笔单价：".$platformBot->per_bishu_usdt_price." USDT</b>\n"
                        // ."👉<b>每笔能量：".$platformBot->per_bishu_energy_quantity."</b>\n"
                        ."✅<b>支付地址：<code>".$platformBot->receive_wallet."</code></b>\n\n"
                        ."👆<b>请点击地址复制，直接转入USDT，如转入 ".($platformBot->per_bishu_usdt_price*100)." USDT，可获得100次免费转账次数</b>\n"
                        ."💰如已在机器人充值USDT，可发送指令：<u>添加笔数 Txx 10</u>，手工添加笔数，Txx为您的波场钱包地址，10为需要添加的笔数";
                        // ."<b>自动监控转入地址能量，不足".$platformBot->per_bishu_energy_quantity."时，自动补足能量</b>";
                
                //群组不能使用
                if(mb_substr($chatid,0,1) == '-'){
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '⏳剩余笔数', 'url' => 'https://t.me/'.$data->bot_username],
                            ]
                        ]
                    ];
                }else{
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '⏳剩余笔数', 'callback_data' => 'energybishusy'],
                            ],
                            [
                                ['text' => '📣地址绑定通知', 'callback_data' => 'energybishubind'],
                                ['text' => '✏️已绑通知地址', 'callback_data' => 'energybishusearch']
                            ],
                            [
                                ['text' => '➕增加地址笔数', 'callback_data' => 'energybishubalanceadd']
                            ]
                        ]
                    ];
                }
                
                $encodedKeyboard = json_encode($keyboard);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
            
            //点击能量的笔数套餐-查地址剩余笔数
            }elseif($message == 'energybishusy' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '查地址剩余能量笔数套餐请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $replytext = "<b>请在2分钟内回复此消息，直接回复您要查询的波场地址</b>\n"
                            ."<b>查询后会回复您该地址购买的能量笔数套餐剩余次数</b>\n\n"
                            ."点击 /start 重新开始";
                
                $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要查询的波场地址"]);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('energybishusyconfirm'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('energybishusyconfirm'.$chatid);
                }
                setexRedis('energybishusyconfirm'.$chatid,$response['message_id'],120);
                
                return '';
                
            //点击能量的笔数套餐-地址绑定通知
            }elseif($message == 'energybishubind' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '地址绑定通知请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $replytext = "<b>请在2分钟内回复此消息，直接回复您要绑定的波场地址</b>\n"
                            ."<b>笔数套餐是通过直接转入USDT下单，如您想要接收地址的能量通知，需要手工绑定。只有下单了笔数套餐的地址才能绑定！</b>\n\n"
                            ."点击 /start 重新开始";
                
                $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要绑定通知的波场地址"]);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('energybishubind'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('energybishubind'.$chatid);
                }
                setexRedis('energybishubind'.$chatid,$response['message_id'],120);
                
                return '';
            
            //点击笔数套餐-增加地址笔数
            }elseif($message == 'energybishubalanceadd' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '该功能请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $replytext = "<b>请在2分钟内回复此消息，直接回复您要增加笔数的波场地址</b>\n"
                            ."<b>增加笔数扣除钱包USDT余额，请保证USDT余额充足</b>\n\n"
                            ."点击 /start 重新开始";
                
                $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要增加笔数的波场地址"]);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('energybishubalanceaddconfirm'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('energybishubalanceaddconfirm'.$chatid);
                }
                setexRedis('energybishubalanceaddconfirm'.$chatid,$response['message_id'],120);
                
                return '';
                
            //点击能量的笔数套餐-已绑通知地址
            }elseif($message == 'energybishusearch' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '查询已绑定通知地址请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $bindWallet = EnergyAiBishu::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->orderBy('rid','desc')->get();
                
                $replytext = "<b>当前已绑定：".$bindWallet->count()." 个</b>\n";
                            
                if($bindWallet->count() > 0){
                    $energyBot = EnergyPlatformBot::where('bot_rid',$bot_rid)->first();
                    
                    $ico = array('0'=>'1️⃣','1'=>'2️⃣','2'=>'3️⃣' ,'3'=>'4️⃣','4'=>'5️⃣' ,'5'=>'6️⃣','6'=>'7️⃣','7'=>'8️⃣' ,'8'=>'9️⃣'  ,'9'=>'🔟' );
                    $i = 0;
                    $replytext = $replytext."绑定地址如下(只显示最新10个)：\n";
                    foreach ($bindWallet as $k => $v) {
                        if($energyBot->bishu_daili_type == 1){
                            $replytext = $replytext.$ico[$i]."  <code>".$v->wallet_addr. "</code> (剩".($v->max_buy_quantity - $v->total_buy_quantity)."笔)\n";
                        }else{
                            $replytext = $replytext.$ico[$i]."  <code>".$v->wallet_addr. "</code>\n";
                        }
                        $i++;
                        if($i >= 9){
                            break;
                        }
                    }
                }
                
                //内联按钮
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                            ['text' => '⏳剩余笔数', 'callback_data' => 'energybishusy'],
                        ],
                        [
                            ['text' => '📣地址绑定通知', 'callback_data' => 'energybishubind'],
                            ['text' => '✏️已绑通知地址', 'callback_data' => 'energybishusearch']
                        ],
                        [
                            ['text' => '➕增加地址笔数', 'callback_data' => 'energybishubalanceadd']
                        ]
                    ]
                ];
                
                $encodedKeyboard = json_encode($keyboard);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
                
            //点击能量的智能托管-查看我的托管地址
            }elseif($message == 'aitrusteeshipmyaddress' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();
                    
                    $replytext = "<b>一个账号最大允许托管10个地址</b>\n"
                                ."自动监控地址能量,不足65000或者131000时,自动给与能量补足\n"
                                ."当前已托管：".$aiWallet->count()." 个\n";
                    
                    if($aiWallet->count() > 0){
                        $ico = array('0'=>'1️⃣','1'=>'2️⃣','2'=>'3️⃣' ,'3'=>'4️⃣','4'=>'5️⃣' ,'5'=>'6️⃣','6'=>'7️⃣','7'=>'8️⃣' ,'8'=>'9️⃣'  ,'9'=>'🔟' );
                        $i = 0;
                        foreach ($aiWallet as $k => $v) {
                            $replytext = $replytext
                                    .$ico[$i]."  <code>".$v->wallet_addr. "</code>\n";
                            $i++;
                            if($i >= 9){
                                break;
                            }
                        }
                    }
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                            ],
                            [
                                ['text' => '⬅️返回上一步', 'callback_data' => 'aitrusteeship'],
                                ['text' => '🔄刷新', 'callback_data' => 'aitrusteeshipmyaddress']
                            ],
                            [
                                ['text' => '⚙️监控能量设置', 'callback_data' => 'aitrusteeshipupdate']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
                
            //点击能量的智能托管-修改托管代理能量数量
            }elseif($message == 'aitrusteeshipupdate' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();
                    
                    if($aiWallet->count() > 0){
                        $replytext = "当前已智能托管地址：".$aiWallet->count()." 个\n"
                                ."请选择要设置的智能托管钱包,可设置监控65000或者131000能量\n"
                                ."65000能量可以给有U的地址转账一次,131000可以给无U的地址转账一次";
                                
                        $keyboardone = [];
                        $keyboard = [];
                        $s = 0;
                        
                        foreach ($aiWallet as $k => $v) {
                            $keyboardone['text'] = $v->wallet_addr." 托管:".$v->min_energy_quantity;
                            $keyboardone['callback_data'] = 'aitrusteeshipupdate_'.$v->rid;
                            
                            $keyboard[$s][] = $keyboardone;
                            $keyboardone = [];
                            $s++;
                        }
                        
                        $keyboardone = [];
                        $keyboardone['text'] = '↩️返回托管列表';
                        $keyboardone['callback_data'] = 'aitrusteeshipmyaddress';
                        $keyboard[$s][] = $keyboardone;
                        
                        $reply_markup = [
                            'inline_keyboard' => $keyboard
                        ];
                        $encodedKeyboard = json_encode($reply_markup);
                    }else{
                        $replytext = "<b>一个账号最大允许托管10个地址</b>\n"
                                ."自动监控地址能量,不足65000或者131000时,自动给与能量补足\n"
                                ."当前已托管：".$aiWallet->count()." 个,请先添加后再修改设置\n";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                    ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                                ],
                                [
                                    ['text' => '⬅️返回上一步', 'callback_data' => 'aitrusteeship'],
                                    ['text' => '🔄刷新', 'callback_data' => 'aitrusteeshipmyaddress']
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
            
            //点击能量的智能托管-修改托管代理能量数量-选择地址
            }elseif(mb_substr($message,0,20) == 'aitrusteeshipupdate_' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $rid = str_replace(['aitrusteeshipupdate_'],'',$message);
                    $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->where('rid',$rid)->first();
                    
                    if($aiWallet){
                        $replytext = "您正在设置智能托管地址：<code>".$aiWallet->wallet_addr."</code>\n"
                                ."当前地址设置的托管能量为：<b>".$aiWallet->min_energy_quantity."</b>\n"
                                ."65000能量可以给有U的地址转账一次,131000可以给无U的地址转账一次\n"
                                ."请选择下方每次需要代理的能量数量,更改后立即生效";
                        
                        $keyboardone = [];
                        $keyboardone['text'] = '托管65000能量';
                        $keyboardone['callback_data'] = 'aitrusteeshipupdateconfirm_'.$rid.'_1';
                        $keyboard[0][] = $keyboardone;
                        $keyboardone = [];
                        $keyboardone['text'] = '托管131000能量';
                        $keyboardone['callback_data'] = 'aitrusteeshipupdateconfirm_'.$rid.'_2';
                        $keyboard[0][] = $keyboardone;
                        $keyboardone = [];
                        $keyboardone['text'] = '⬅️返回上一步';
                        $keyboardone['callback_data'] = 'aitrusteeshipupdate';
                        $keyboard[1][] = $keyboardone;
                        
                        $reply_markup = [
                            'inline_keyboard' => $keyboard
                        ];
                        $encodedKeyboard = json_encode($reply_markup);
                    }else{
                        $replytext = "<b>当前选择的智能托管地址不存在,请重新选择</b>";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                    ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                                ],
                                [
                                    ['text' => '⬅️返回上一步', 'callback_data' => 'aitrusteeship'],
                                    ['text' => '🔄刷新', 'callback_data' => 'aitrusteeshipmyaddress']
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
                
            //点击能量的智能托管-修改托管代理能量数量-确定修改
            }elseif(mb_substr($message,0,27) == 'aitrusteeshipupdateconfirm_' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $explodeArr = explode("_", $message);
                    $rid = $explodeArr[1] ?? 0;
                    $updateid = $explodeArr[2] ?? 1;
                    
                    $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->where('rid',$rid)->first();
                    
                    if($aiWallet){
                        $minEnergy = $updateid == 1 ?65000:131000;
                        EnergyAiTrusteeship::where('rid',$rid)->update(['min_energy_quantity' => $minEnergy,'per_buy_energy_quantity' => $minEnergy]);
                        $replytext = "您的智能托管地址设置成功：<code>".$aiWallet->wallet_addr."</code>\n"
                                ."当前地址设置的托管能量为：<b>".$minEnergy."</b>\n"
                                ."65000能量可以给有U的地址转账一次,131000可以给无U的地址转账一次\n";
                        
                        $keyboardone['text'] = '⬅️返回上一步';
                        $keyboardone['callback_data'] = 'aitrusteeshipupdate';
                        $keyboard[0][] = $keyboardone;
                        
                        $reply_markup = [
                            'inline_keyboard' => $keyboard
                        ];
                        $encodedKeyboard = json_encode($reply_markup);
                    }else{
                        $replytext = "<b>当前选择的智能托管地址不存在,请重新选择</b>";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                    ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                                ],
                                [
                                    ['text' => '⬅️返回上一步', 'callback_data' => 'aitrusteeship'],
                                    ['text' => '🔄刷新', 'callback_data' => 'aitrusteeshipmyaddress']
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
                
            //点击能量的智能托管-添加托管地址
            }elseif($message == 'aitrusteeshipaddaddress' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();
                    $sy_tuoguan = 10 - $aiWallet->count();
                    
                    $tuoguan = $sy_tuoguan > 0 ?"还可以托管：".$sy_tuoguan." 个\n<b>请在2分钟内回复此消息已激活的波场地址</b>":"已无法添加,可删除无需托管的地址后再添加";
                    $replytext = "<b>一个账号最大允许托管10个地址</b>\n"
                                ."当前已托管：".$aiWallet->count()." 个\n"
                                .$tuoguan."\n\n"
                                ."点击 /start 重新开始";
                    
                    if($sy_tuoguan > 0){
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要托管的波场地址，一定要激活后的地址"]);
                    }else{
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>false,'input_field_placeholder'=>"当前无可用数量"]);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('aitrusteeshipaddaddress'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('aitrusteeshipaddaddress'.$chatid);
                }
                setexRedis('aitrusteeshipaddaddress'.$chatid,$response['message_id'],120);
                
                return '';
                
            //点击能量的智能托管-删除托管地址
            }elseif($message == 'aitrusteeshipdeleteaddress' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $aiWallet = EnergyAiTrusteeship::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->get();

                    if($aiWallet->count() > 0){
                        $replytext = "<b>请在2分钟内回复此消息需要删除的波场地址</b>\n"
                                ."当前已托管：".$aiWallet->count()." 个\n\n"
                                ."点击 /start 重新开始";
                                
                        $ico = array('0'=>'1️⃣','1'=>'2️⃣','2'=>'3️⃣' ,'3'=>'4️⃣','4'=>'5️⃣' ,'5'=>'6️⃣','6'=>'7️⃣','7'=>'8️⃣' ,'8'=>'9️⃣'  ,'9'=>'🔟' );
                        $i = 0;
                        foreach ($aiWallet as $k => $v) {
                            $replytext = $replytext
                                    .$ico[$i]."  <code>".$v->wallet_addr. "</code>\n";
                            $i++;
                        }
                        
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要删除的托管地址"]);
                    }else{
                        $replytext = "<b>当前账号无托管地址</b>\n";
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>false,'input_field_placeholder'=>"当前无智能托管地址"]);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('aitrusteeshipdeleteaddress'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('aitrusteeshipdeleteaddress'.$chatid);
                }
                setexRedis('aitrusteeshipdeleteaddress'.$chatid,$response['message_id'],120);
                
                return '';
            
            //点击监控钱包
            }elseif(mb_substr($message,0,14) == 'monitorwallet_' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '监控钱包请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行钱包监控";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $explodeArr = explode("_", $message);

                    $page = implode("_", array_slice($explodeArr, 1)) ?? 1;
                    $limit = 10;
                    
                    $monitorWalletModel = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid);
                    $count = $monitorWalletModel->count();
                    $offset = $page ? ($page - 1) * $limit : 0;
                    $totalpage = ceil($count / $limit);
                    
                    $monitorWallet = $monitorWalletModel->limit($limit)->offset($offset)->orderBy('rid','desc')->get();
                    
                    $replytext = "<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."<b>剩余可监控数量：</b><code>".($botuser->max_monitor_wallet - $count)."</code>\n"
                            ."<b>可点击下方购买监控套餐！</b>\n"
                            ."当前已监控地址：".$count." 个\n";
                    
                    foreach ($monitorWallet as $k => $v) {
                        $replytext = $replytext."  <code>".$v->monitor_wallet. "</code>\n";
                    }
                    $keyboard = [];
                    $keyboardone = [];
                    $keyboardone['text'] = '➕添加监控地址';
                    $keyboardone['callback_data'] = 'monitorddaddress';
                    $keyboard[0][] = $keyboardone;
                    $keyboardone = [];
                    $keyboardone['text'] = '➖删除监控地址';
                    $keyboardone['callback_data'] = 'monitordeleteaddress';
                    $keyboard[0][] = $keyboardone;
                    
                    $keyboardone = [];
                    $keyboardone['text'] = '💎购买监控包';
                    $keyboardone['callback_data'] = 'monitorwalletbuy';
                    $keyboard[1][] = $keyboardone;
                    $keyboardone = [];
                    $keyboardone['text'] = '👨联系客服';
                    $keyboardone['url'] = 'https://t.me/'.mb_substr($data->bot_admin_username,1);
                    $keyboard[1][] = $keyboardone;
                    
                    
                    if($page != 1){
                        $keyboardone = [];
                        $keyboardone['text'] = '⬅️上一页';
                        $keyboardone['callback_data'] = 'monitorwallet_'.($page-1);
                        $keyboard[2][] = $keyboardone;
                    }
                    if($page < $totalpage){
                        $keyboardone = [];
                        $keyboardone['text'] = '➡️下一页';
                        $keyboardone['callback_data'] = 'monitorwallet_'.($page+1);
                        $keyboard[2][] = $keyboardone;
                    }
                    $keyboardone = [];
                    $keyboardone['text'] = '🛠修改备注';
                    $keyboardone['callback_data'] = 'monitorwalletupdate_1';
                    $keyboard[2][] = $keyboardone;
                    $keyboardone = [];
                    $keyboardone['text'] = '🛠修改监控';
                    $keyboardone['callback_data'] = 'monitorwalletfunc_1';
                    $keyboard[2][] = $keyboardone;
                    $reply_markup = [
                        'inline_keyboard' => $keyboard
                    ];
                    $encodedKeyboard = json_encode($reply_markup);
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
            
            //点击监控钱包-修改
            }elseif(mb_substr($message,0,26) == 'monitorwalletupdatewallet_' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '监控钱包修改请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行钱包监控";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $walletrid = str_replace(['monitorwalletupdatewallet_'],'',$message);
                    $monitorWalletModel = MonitorWallet::where('rid',$walletrid)->first();
                    if(empty($monitorWalletModel)){
                        $replytext = "钱包已不存在,请重新选择";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }else{
                        $replytext = "正在设置地址: <code>".$monitorWalletModel->monitor_wallet."</code>\n"
                                    ."<b>请在2分钟内回复此消息，输入50个汉字内的备注</b>\n"
                                    ."<b>直接发送文字无效</b>\n\n"
                                    ."点击 /start 重新开始";
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入备注,不超过50个汉字"]);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('monitorwalletupdatewalletconfirm'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('monitorwalletupdatewalletconfirm'.$chatid);
                }
                setexRedis('monitorwalletupdatewalletconfirm'.$chatid,$response['message_id']."_".$walletrid,120);
                
                return '';
            
            //点击监控钱包-修改功能-选择了钱包
            }elseif(mb_substr($message,0,24) == 'monitorwalletupdatefunc_' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '监控钱包修改请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行钱包监控";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $walletrid = str_replace(['monitorwalletupdatefunc_'],'',$message);
                    $monitorWalletModel = MonitorWallet::where('rid',$walletrid)->first();
                    if(empty($monitorWalletModel)){
                        $replytext = "钱包已不存在,请重新选择";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }else{
                        $replytext = "正在设置地址: <code>".$monitorWalletModel->monitor_wallet."</code>\n"
                                    ."当前地址监控功能如下：\n"
                                    ."USDT交易监听：<u>".($monitorWalletModel->monitor_usdt_transaction == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."TRX交易监听：<u>".($monitorWalletModel->monitor_trx_transaction == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."授权监听：<u>".($monitorWalletModel->monitor_approve_transaction == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."多签监听：<u>".($monitorWalletModel->monitor_multi_transaction == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."能量监听：<u>".($monitorWalletModel->monitor_pledge_transaction == 'YY' ?'✅已开启':'❌已关闭')."</u>\n\n"
                                    ."<b>点击下面功能即可开启/关闭功能</b>";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => 'USDT交易监听', 'callback_data' => 'monitorwalletupdatefuncusdt_'.$monitorWalletModel->rid],
                                    ['text' => 'TRX交易监听', 'callback_data' => 'monitorwalletupdatefunctrxa_'.$monitorWalletModel->rid]
                                ],
                                [
                                    ['text' => '授权监听', 'callback_data' => 'monitorwalletupdatefuncappr_'.$monitorWalletModel->rid],
                                    ['text' => '多签监听', 'callback_data' => 'monitorwalletupdatefuncmult_'.$monitorWalletModel->rid],
                                    ['text' => '能量监听', 'callback_data' => 'monitorwalletupdatefuncpled_'.$monitorWalletModel->rid]
                                ],
                                [
                                    ['text' => '↩️返回监控列表', 'callback_data' => 'monitorwallet_1'],
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
                
            //点击监控钱包-修改功能
            }elseif(in_array(mb_substr($message,0,28),['monitorwalletupdatefuncusdt_','monitorwalletupdatefunctrxa_','monitorwalletupdatefuncappr_','monitorwalletupdatefuncmult_','monitorwalletupdatefuncpled_']) && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '监控钱包修改请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行钱包监控";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $walletrid = str_replace(['monitorwalletupdatefuncusdt_','monitorwalletupdatefunctrxa_','monitorwalletupdatefuncappr_','monitorwalletupdatefuncmult_','monitorwalletupdatefuncpled_'],'',$message);
                    $monitorWalletModel = MonitorWallet::where('rid',$walletrid)->first();
                    if(empty($monitorWalletModel)){
                        $replytext = "钱包已不存在,请重新选择";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }else{
                        $save_data = [];
                        $save_data['monitor_usdt_transaction'] = $monitorWalletModel->monitor_usdt_transaction;
                        $save_data['monitor_trx_transaction'] = $monitorWalletModel->monitor_trx_transaction;
                        $save_data['monitor_approve_transaction'] = $monitorWalletModel->monitor_approve_transaction;
                        $save_data['monitor_multi_transaction'] = $monitorWalletModel->monitor_multi_transaction;
                        $save_data['monitor_pledge_transaction'] = $monitorWalletModel->monitor_pledge_transaction;
                        
                        switch (mb_substr($message,0,28)) {
                            case 'monitorwalletupdatefuncusdt_':
                                $save_data['monitor_usdt_transaction'] = $monitorWalletModel->monitor_usdt_transaction == 'YY' ?'NN':'YY';
                                break;
                            case 'monitorwalletupdatefunctrxa_':
                                $save_data['monitor_trx_transaction'] = $monitorWalletModel->monitor_trx_transaction == 'YY' ?'NN':'YY';
                                break;
                            case 'monitorwalletupdatefuncappr_':
                                $save_data['monitor_approve_transaction'] = $monitorWalletModel->monitor_approve_transaction == 'YY' ?'NN':'YY';
                                break;
                            case 'monitorwalletupdatefuncmult_':
                                $save_data['monitor_multi_transaction'] = $monitorWalletModel->monitor_multi_transaction == 'YY' ?'NN':'YY';
                                break;
                            case 'monitorwalletupdatefuncpled_':
                                $save_data['monitor_pledge_transaction'] = $monitorWalletModel->monitor_pledge_transaction == 'YY' ?'NN':'YY';
                                break;
                            default:
                                // code...
                                break;
                        }
                        MonitorWallet::where('rid',$walletrid)->update($save_data);
                        
                        $replytext = "✅已设置成功！！\n"
                                    ."设置地址: <code>".$monitorWalletModel->monitor_wallet."</code>\n"
                                    ."当前地址监控功能如下：\n"
                                    ."USDT交易监听：<u>".($save_data['monitor_usdt_transaction'] == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."TRX交易监听：<u>".($save_data['monitor_trx_transaction'] == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."授权监听：<u>".($save_data['monitor_approve_transaction'] == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."多签监听：<u>".($save_data['monitor_multi_transaction'] == 'YY' ?'✅已开启':'❌已关闭')."</u>\n"
                                    ."能量监听：<u>".($save_data['monitor_pledge_transaction'] == 'YY' ?'✅已开启':'❌已关闭')."</u>\n\n"
                                    ."<b>点击下面功能即可开启/关闭功能</b>";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => 'USDT交易监听', 'callback_data' => 'monitorwalletupdatefuncusdt_'.$monitorWalletModel->rid],
                                    ['text' => 'TRX交易监听', 'callback_data' => 'monitorwalletupdatefunctrxa_'.$monitorWalletModel->rid]
                                ],
                                [
                                    ['text' => '授权监听', 'callback_data' => 'monitorwalletupdatefuncappr_'.$monitorWalletModel->rid],
                                    ['text' => '多签监听', 'callback_data' => 'monitorwalletupdatefuncmult_'.$monitorWalletModel->rid],
                                    ['text' => '能量监听', 'callback_data' => 'monitorwalletupdatefuncpled_'.$monitorWalletModel->rid]
                                ],
                                [
                                    ['text' => '↩️返回监控列表', 'callback_data' => 'monitorwallet_1'],
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
                
            //点击监控钱包-修改
            }elseif(mb_substr($message,0,20) == 'monitorwalletupdate_' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '监控钱包修改请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行钱包监控";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $explodeArr = explode("_", $message);

                    $page = implode("_", array_slice($explodeArr, 1)) ?? 1;
                    $limit = 10;
                    
                    $monitorWalletModel = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid);
                    $count = $monitorWalletModel->count();
                    $offset = $page ? ($page - 1) * $limit : 0;
                    $totalpage = ceil($count / $limit);
                    
                    $monitorWallet = $monitorWalletModel->limit($limit)->offset($offset)->orderBy('rid','desc')->get();
                    
                    $replytext = "当前已监控地址：".$count." 个\n"
                                ."请选择要设置的监控钱包";
                                
                    $keyboardone = [];
                    $keyboard = [];
                    $s = 0;
                    
                    foreach ($monitorWallet as $k => $v) {
                        $keyboardone['text'] = $v->monitor_wallet;
                        $keyboardone['callback_data'] = 'monitorwalletupdatewallet_'.$v->rid;
                        
                        $keyboard[$s][] = $keyboardone;
                        $keyboardone = [];
                        $s++;
                    }
                    if($page != 1){
                        $keyboardone = [];
                        $keyboardone['text'] = '⬅️上一页';
                        $keyboardone['callback_data'] = 'monitorwalletupdate_'.($page-1);
                        $keyboard[$s][] = $keyboardone;
                    }
                    if($page < $totalpage){
                        $keyboardone = [];
                        $keyboardone['text'] = '➡️下一页';
                        $keyboardone['callback_data'] = 'monitorwalletupdate_'.($page+1);
                        $keyboard[$s][] = $keyboardone;
                    }
                    $keyboardone = [];
                    $keyboardone['text'] = '↩️返回监控列表';
                    $keyboardone['callback_data'] = 'monitorwallet_1';
                    $keyboard[$s][] = $keyboardone;
                    
                    $reply_markup = [
                        'inline_keyboard' => $keyboard
                    ];
                    $encodedKeyboard = json_encode($reply_markup);
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
            
            //点击监控钱包-修改监控功能
            }elseif(mb_substr($message,0,18) == 'monitorwalletfunc_' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '监控钱包修改请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行钱包监控";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $explodeArr = explode("_", $message);

                    $page = implode("_", array_slice($explodeArr, 1)) ?? 1;
                    $limit = 10;
                    
                    $monitorWalletModel = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid);
                    $count = $monitorWalletModel->count();
                    $offset = $page ? ($page - 1) * $limit : 0;
                    $totalpage = ceil($count / $limit);
                    
                    $monitorWallet = $monitorWalletModel->limit($limit)->offset($offset)->orderBy('rid','desc')->get();
                    
                    $replytext = "当前已监控地址：".$count." 个\n"
                                ."可修改USDT交易监控,TRX交易监控,授权监控,多签监控,质押代理监控\n"
                                ."<b>请选择要设置的监控钱包</b>";
                                
                    $keyboardone = [];
                    $keyboard = [];
                    $s = 0;
                    
                    foreach ($monitorWallet as $k => $v) {
                        $keyboardone['text'] = $v->monitor_wallet;
                        $keyboardone['callback_data'] = 'monitorwalletupdatefunc_'.$v->rid;
                        
                        $keyboard[$s][] = $keyboardone;
                        $keyboardone = [];
                        $s++;
                    }
                    if($page != 1){
                        $keyboardone = [];
                        $keyboardone['text'] = '⬅️上一页';
                        $keyboardone['callback_data'] = 'monitorwalletfunc_'.($page-1);
                        $keyboard[$s][] = $keyboardone;
                    }
                    if($page < $totalpage){
                        $keyboardone = [];
                        $keyboardone['text'] = '➡️下一页';
                        $keyboardone['callback_data'] = 'monitorwalletfunc_'.($page+1);
                        $keyboard[$s][] = $keyboardone;
                    }
                    $keyboardone = [];
                    $keyboardone['text'] = '↩️返回监控列表';
                    $keyboardone['callback_data'] = 'monitorwallet_1';
                    $keyboard[$s][] = $keyboardone;
                    
                    $reply_markup = [
                        'inline_keyboard' => $keyboard
                    ];
                    $encodedKeyboard = json_encode($reply_markup);
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
                
            //点击监控钱包-购买钱包套餐
            }elseif($message == 'monitorwalletbuy' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '监控钱包请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }

                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行购买";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $monitorWallet = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid)->get();
                    
                    $replytext = "<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."<b>总可监控数量：</b><code>".$botuser->max_monitor_wallet."</code>\n"
                            ."<b>剩余可监控数量：</b><code>".($botuser->max_monitor_wallet - $monitorWallet->count())."</code>\n"
                            ."<b>点击下方购买监控套餐数量！</b>";

                    $monitorBot = MonitorBot::where('bot_rid',$bot_rid)->where('status',0)->first();
                    if(!empty($monitorBot)){
                        $keyboardone = [];
                        $keyboard = [];
                        $s = 0;
                        for($i=1;$i<=6;$i++){
                            $add = 0;
                            switch ($i) {
                                case 1:
                                    if($monitorBot->price_usdt_5 > 0){
                                        $keyboardone['text'] = '🔥5个('.($monitorBot->price_usdt_5 + 0).' U)';
                                        $keyboardone['callback_data'] = 'buywalletmonitor5';
                                        $add = 1;
                                    }
                                    break;
                                case 2:
                                    if($monitorBot->price_usdt_10 > 0){
                                        $keyboardone['text'] = '🔥10个('.($monitorBot->price_usdt_10 + 0).' U)';
                                        $keyboardone['callback_data'] = 'buywalletmonitor10';
                                        $add = 1;
                                    }
                                    
                                    break;
                                case 3:
                                    if($monitorBot->price_usdt_20 > 0){
                                        $keyboardone['text'] = '🔥20个('.($monitorBot->price_usdt_20 + 0).' U)';
                                        $keyboardone['callback_data'] = 'buywalletmonitor20';
                                        $add = 1;
                                    }
                                    break;
                                case 4:
                                    if($monitorBot->price_usdt_50 > 0){
                                        $keyboardone['text'] = '🔥50个('.($monitorBot->price_usdt_50 + 0).' U)';
                                        $keyboardone['callback_data'] = 'buywalletmonitor50';
                                        $add = 1;
                                    }
                                    break;
                                case 5:
                                    if($monitorBot->price_usdt_100 > 0){
                                        $keyboardone['text'] = '🔥100个('.($monitorBot->price_usdt_100 + 0).' U)';
                                        $keyboardone['callback_data'] = 'buywalletmonitor100';
                                        $add = 1;
                                    }
                                    break;
                                case 6:
                                    if($monitorBot->price_usdt_200 > 0){
                                        $keyboardone['text'] = '🔥200个('.($monitorBot->price_usdt_200 + 0).' U)';
                                        $keyboardone['callback_data'] = 'buywalletmonitor200';
                                        $add = 1;
                                    }
                                    break;
                                default:
                                    // code...
                                    break;
                            }
                            if($add == 1){
                                if(!empty($keyboard)){
                                    if(count($keyboard[$s]) == 3){
                                        $s++;
                                    }
                                }
                                $keyboard[$s][] = $keyboardone;
                                $keyboardone = [];
                            }
                        }
                        $last = $s + 1;
                        $keyboardone = [];
                        $keyboardone['text'] = '⬅️返回上一步';
                        $keyboardone['callback_data'] = 'monitorwallet_1';
                        $keyboard[$last][] = $keyboardone;
                        $keyboardone = [];
                        $keyboardone['text'] = '👨联系客服';
                        $keyboardone['url'] = 'https://t.me/'.mb_substr($data->bot_admin_username,1);
                        $keyboard[$last][] = $keyboardone;

                        $reply_markup = [
                            'inline_keyboard' => $keyboard
                        ];
                        $encodedKeyboard = json_encode($reply_markup);
                    }else{
                        $replytext = "<b>当前机器人未配置监控套餐，请联系管理员</b>\n";
                            //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '⬅️返回上一步', 'callback_data' => 'monitorwallet_1'],
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
                
            //点击监控钱包-删除监控地址
            }elseif($message == 'monitordeleteaddress' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '删除监控钱包请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行监控地址";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $monitorWallet = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid)->get();

                    if($monitorWallet->count() > 0){
                        $replytext = "<b>请在2分钟内回复此消息需要删除的波场监控地址</b>\n"
                                ."当前已监控：".$monitorWallet->count()." 个\n"
                                ."剩余可监控：".($botuser->max_monitor_wallet - $monitorWallet->count())." 个\n\n"
                                ."点击 /start 重新开始";
                        
                        foreach ($monitorWallet as $k => $v) {
                            $replytext = $replytext."  <code>".$v->wallet_addr. "</code>\n";
                        }
                        
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要删除的监控地址"]);
                    }else{
                        $replytext = "<b>当前账号无监控地址</b>\n";
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>false,'input_field_placeholder'=>"当前无监控地址"]);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('monitordeleteaddress'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('monitordeleteaddress'.$chatid);
                }
                setexRedis('monitordeleteaddress'.$chatid,$response['message_id'],120);
                
                return '';
            
            //点击监控钱包-添加监控地址
            }elseif($message == 'monitorddaddress' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '添加监控钱包请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行监控地址";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $monitorWallet = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid)->get();
                    $sy_tuoguan = $botuser->max_monitor_wallet - $monitorWallet->count();
                    
                    $tuoguan = $sy_tuoguan > 0 ?"还可以监控：".$sy_tuoguan." 个\n<b>请在2分钟内回复此消息波场地址</b>":"已无法添加,可购买监控包或者删除无需监控的地址后再添加";
                    $replytext = "<b>当前已监控：</b>".$monitorWallet->count()." 个\n"
                                .$tuoguan."\n\n"
                                ."点击 /start 重新开始";
                    
                    if($sy_tuoguan > 0){
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要监控的波场地址"]);
                    }else{
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>false,'input_field_placeholder'=>"当前无可用数量"]);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('monitorddaddress'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('monitorddaddress'.$chatid);
                }
                setexRedis('monitorddaddress'.$chatid,$response['message_id'],120);
                
                return '';
            
            //点击能量的智能托管-usdt转换trx
            }elseif($message == 'aitrusteeshipusdtswaptrx' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{

                    if($botuser['cash_usdt'] >= 1){
                        $replytext = "<b>请在2分钟内回复此消息需要转换的USDT数量</b>\n"
                                ."<b>当前USDT余额：</b><code>".$botuser['cash_usdt']."</code>\n\n"
                                ."<b>请输入 1 个USDT以上的数量！</b>\n\n"
                                ."点击 /start 重新开始";
                        
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要转换的USDT数量"]);
                    }else{
                        $replytext = "<b>转换失败，请充值后转换，当前USDT余额：</b>".$botuser['cash_usdt']."\n"
                                    ."USDT大于1时才能转换为TRX！";
                        $encodedKeyboard = Keyboard::forceReply(['force_reply'=>false,'input_field_placeholder'=>"转换失败，请充值后转换"]);
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                #设置缓存
                $add_ai_address = getRedis('aitrusteeshipusdtswaptrx'.$chatid);
                if(!empty($add_ai_address)){
                    deleteRedis('aitrusteeshipusdtswaptrx'.$chatid);
                }
                setexRedis('aitrusteeshipusdtswaptrx'.$chatid,$response['message_id'],120);
                
                return '';
                
            //点击能量的智能托管-充值trx充值usdt
            }elseif(in_array($message,['aitrusteeshiprechargetrx','aitrusteeshiprechargeusdt']) && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $replytext = "<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."⚠️TRX余额不足时，不再执行智能托管，请及时充值\n"
                            ."⚠️充值的USDT可转换为相应价值的TRX，能量使用TRX购买\n"
                            ."<b>请保证余额充足,点击下方可充值余额！</b>";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '10 TRX', 'callback_data' => 'aitrusteeshiprechargetrx10'],
                                ['text' => '100 TRX', 'callback_data' => 'aitrusteeshiprechargetrx100'],
                                ['text' => '500 TRX', 'callback_data' => 'aitrusteeshiprechargetrx500']
                            ],
                            [
                                ['text' => '1000 TRX', 'callback_data' => 'aitrusteeshiprechargetrx1000'],
                                ['text' => '2000 TRX', 'callback_data' => 'aitrusteeshiprechargetrx2000'],
                                ['text' => '5000 TRX', 'callback_data' => 'aitrusteeshiprechargetrx5000']
                            ],
                            [
                                ['text' => '10 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt10'],
                                ['text' => '100 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt100'],
                                ['text' => '500 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt500']
                            ],
                            [
                                ['text' => '1000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt1000'],
                                ['text' => '2000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt2000'],
                                ['text' => '5000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt5000']
                            ],
                            [
                                ['text' => '⬅️返回上一步', 'callback_data' => 'aitrusteeship']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
            
            //购买商品-购买确定
            }elseif(mb_substr($message,0,20) == 'buygoodscdkeyconfirm' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '购买商品请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行购买";
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $choosetype = strpos($message, 'trx') !== false ?'trx':'usdt';
                    $message = str_replace(['buygoodscdkeyconfirmtrx','buygoodscdkeyconfirmusdt'],'',$message);
                    //查cdkey价格
                    $shopcdkeymodel = ShopGoodsCdkey::from('t_shop_goods_cdkey as a')
                            ->join('t_shop_goods as b','a.goods_rid','b.rid')
                            ->join('t_shop_goods_bot as c','c.goods_rid','b.rid')
                            ->where('a.status',1)
                            ->where('b.status',0)
                            ->where('c.status',0)
                            ->where('a.rid',$message)
                            ->select('a.cdkey_usdt_price','a.cdkey_trx_price','b.goods_usdt_price','b.goods_trx_price','c.goods_usdt_discount','c.goods_trx_discount','a.cdkey_no','a.cdkey_pwd','a.rid')
                            ->first();

                    if(empty($shopcdkeymodel)){
                        $replytext = "❌该商品已无法购买,请选择其他商品";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }else{
                        $trx_price = ($shopcdkeymodel->cdkey_trx_price > 0 ?$shopcdkeymodel->cdkey_trx_price:($shopcdkeymodel->goods_trx_price > 0 ?$shopcdkeymodel->goods_trx_price:0)) * $shopcdkeymodel->goods_trx_discount;
                        $usdt_price = ($shopcdkeymodel->cdkey_usdt_price > 0 ?$shopcdkeymodel->cdkey_usdt_price:($shopcdkeymodel->goods_usdt_price > 0 ?$shopcdkeymodel->goods_usdt_price:0)) * $shopcdkeymodel->goods_usdt_discount;

                        if($trx_price == 0 && $usdt_price == 0){
                            $replytext = "❌该商品已无法购买,请选择其他商品";
                            //内联按钮
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                    ]
                                ]
                            ];
                            $encodedKeyboard = json_encode($keyboard);
                        }else{
                            $isSuccess = 'N';
                            if($choosetype == 'trx' && floatval($botuser->cash_trx) >= floatval($trx_price)){
                                $isSuccess = 'Y';
                                $paytype = 1;
                                TelegramBotUser::where('rid',$botuser->rid)->update(['cash_trx' => $botuser->cash_trx - $trx_price]);
                            }elseif($choosetype == 'usdt' && floatval($botuser->cash_usdt) >= floatval($usdt_price)){
                                $isSuccess = 'Y';
                                $paytype = 2;
                                TelegramBotUser::where('rid',$botuser->rid)->update(['cash_usdt' => $botuser->cash_usdt - $usdt_price]);
                            }

                            if($isSuccess == 'Y'){
                                ShopGoodsCdkey::where('rid',$shopcdkeymodel->rid)->update(['status' => 2]);
                                
                                $insert_data = [];
                                $insert_data['bot_rid'] = $bot_rid;
                                $insert_data['tg_uid'] = $chatid;
                                $insert_data['tg_username'] = $request->callback_query['from']['username'] ?? '';
                                $insert_data['cdkey_no'] = $shopcdkeymodel->cdkey_no;
                                $insert_data['cdkey_pwd'] = $shopcdkeymodel->cdkey_pwd;
                                $insert_data['pay_type'] = $paytype;
                                $insert_data['pay_price'] = $paytype == 1 ?$trx_price:$usdt_price;	
                                $insert_data['pay_time'] = nowDate();
                                
                                ShopOrder::insertGetId($insert_data);
                                
                                $rsa_services = new RsaServices();
                                $cdkeydecrypt = $rsa_services->privateDecrypt($shopcdkeymodel->cdkey_pwd);
                                $replytext = "<b>✅恭喜您，已成功购买商品</b>：\n"
                                ."<b>购买商品</b>：<code>".$shopcdkeymodel->cdkey_no."</code>\n"
                                ."<b>卡密</b>：<code>".$cdkeydecrypt."</code>\n"
                                ."<b>⚠️请注意保存商品信息，该卡密只显示一次！！</b>";
                            }else{
                                $replytext = "<b>❌购买失败,请查看余额是否足够</b>：\n"
                                        ."当前购买商品：<code>".$shopcdkeymodel->cdkey_no."</code>\n"
                                        ."购买需要支付TRX：".($trx_price == 0 ?'不支持':$trx_price)." 或者支付USDT：".($usdt_price == 0 ?'不支持':$usdt_price);
                            }

                            $keyboardone = [];
                            $keyboardone['text'] = '🔥我要充值';
                            $keyboardone['callback_data'] = 'aitrusteeshiprechargetrx';
                            $keyboard[0][] = $keyboardone;
                            $keyboardone = [];
                            $keyboardone['text'] = '👨联系客服';
                            $keyboardone['url'] = 'https://t.me/'.mb_substr($data->bot_admin_username,1);
                            $keyboard[0][] = $keyboardone;
                            $keyboardone = [];
                            $reply_markup = [
                                'inline_keyboard' => $keyboard
                            ];
                            $encodedKeyboard = json_encode($reply_markup);
                        }
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';

            //购买商品-购买下单
            }elseif(mb_substr($message,0,13) == 'buygoodscdkey' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '购买商品请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行购买";
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $message = str_replace(['buygoodscdkey'],'',$message);
                    //查cdkey价格
                    $shopcdkeymodel = ShopGoodsCdkey::from('t_shop_goods_cdkey as a')
                            ->join('t_shop_goods as b','a.goods_rid','b.rid')
                            ->join('t_shop_goods_bot as c','c.goods_rid','b.rid')
                            ->where('a.status',1)
                            ->where('b.status',0)
                            ->where('c.status',0)
                            ->where('a.rid',$message)
                            ->select('a.cdkey_usdt_price','a.cdkey_trx_price','b.goods_usdt_price','b.goods_trx_price','c.goods_usdt_discount','c.goods_trx_discount','a.cdkey_no')
                            ->first();

                    if(empty($shopcdkeymodel)){
                        $replytext = "❌该商品已无法购买,请选择其他商品";
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }else{
                        $trx_price = ($shopcdkeymodel->cdkey_trx_price > 0 ?$shopcdkeymodel->cdkey_trx_price:($shopcdkeymodel->goods_trx_price > 0 ?$shopcdkeymodel->goods_trx_price:0)) * $shopcdkeymodel->goods_trx_discount;
                        $usdt_price = ($shopcdkeymodel->cdkey_usdt_price > 0 ?$shopcdkeymodel->cdkey_usdt_price:($shopcdkeymodel->goods_usdt_price > 0 ?$shopcdkeymodel->goods_usdt_price:0)) * $shopcdkeymodel->goods_usdt_discount;

                        if($trx_price == 0 && $usdt_price == 0){
                            $replytext = "❌该商品已无法购买,请选择其他商品";
                            //内联按钮
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                    ]
                                ]
                            ];
                            $encodedKeyboard = json_encode($keyboard);
                        }else{
                            $replytext = "您正在购买商品：\n"
                                        ."<code>".$shopcdkeymodel->cdkey_no."</code>\n"
                                        ."购买需要支付TRX：".($trx_price == 0 ?'不支持':$trx_price)." 或者支付USDT：".($usdt_price == 0 ?'不支持':$usdt_price)."\n\n"
                                        ."<b>请选择支付方式</b>\n"
                                        ."<b>TRX 余额</b>：<code>".($botuser->cash_trx + 0)."</code>\n"
                                        ."<b>USDT 余额</b>：<code>".($botuser->cash_usdt + 0)."</code>";
                            $keyboardone = [];
                            $keyboardone['text'] = 'TRX 余额支付';
                            $keyboardone['callback_data'] = 'buygoodscdkeyconfirmtrx'.$message;
                            $keyboard[0][] = $keyboardone;
                            $keyboardone = [];
                            $keyboardone['text'] = 'USDT 余额支付';
                            $keyboardone['callback_data'] = 'buygoodscdkeyconfirmusdt'.$message;
                            $keyboard[0][] = $keyboardone;
                            
                            $reply_markup = [
                                'inline_keyboard' => $keyboard
                            ];
                            $encodedKeyboard = json_encode($reply_markup);
                        }
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';

            //购买商品-查cdkey
            }elseif(mb_substr($message,0,8) == 'buygoods' && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '购买商品请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再进行购买";
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $message = str_replace(['buygoods'],'',$message);
                    $explodeArr = explode("_", $message);
                    $goodsRid = implode("_", array_slice($explodeArr, 0, 1));
                    
                    $page = implode("_", array_slice($explodeArr, 1));
                    $limit = 10;

                    $shopmodel = ShopGoodsCdkey::from('t_shop_goods_cdkey as a')
                            ->join('t_shop_goods as b','a.goods_rid','b.rid')
                            ->where('a.status',1)
                            ->where('b.status',0)
                            ->where('b.rid',$goodsRid);
            
                    $count = $shopmodel->count();
                    $offset = $page ? ($page - 1) * $limit : 0;
                    $totalpage = ceil($count / $limit);
            
                    $shopGoodsCdkey = $shopmodel->limit($limit)->offset($offset)->select('a.rid','a.cdkey_no','b.show_notes')->orderBy('a.seq_sn','desc')->orderBy('a.rid','desc')->get();
                    
                    $keyboardone = [];
                    $keyboard = [];
                    $s = 0;
                    $add = 'N';
                    if($shopGoodsCdkey->count() > 0){
                        $add = 'Y';
                        $replytext = "请选择商品\n".$shopGoodsCdkey[0]['show_notes'];
                        
                        foreach ($shopGoodsCdkey as $k => $v) {
                            //内联按钮
                            $keyboardone['text'] = $v->cdkey_no;
                            $keyboardone['callback_data'] = 'buygoodscdkey'.$v->rid;
                            
                            if(!empty($keyboard)){
                                if(count($keyboard[$s]) == 1){
                                    $s++;
                                }
                            }
                            
                            $keyboard[$s][] = $keyboardone;
                            $keyboardone = [];
                        }
                    }else{
                        $replytext = "<b>当前商品不可购买！</b>";
                    }
                    
                    $last = $add == 'N'?0:($s + 1);
                    $keyboardone = [];
                    $keyboardone['text'] = '🔥我要充值';
                    $keyboardone['callback_data'] = 'aitrusteeshiprechargetrx';
                    $keyboard[$last][] = $keyboardone;
                    $keyboardone = [];
                    $keyboardone['text'] = '👨联系客服';
                    $keyboardone['url'] = 'https://t.me/'.mb_substr($data->bot_admin_username,1);
                    $keyboard[$last][] = $keyboardone;
                    
                    if($add == 'Y'){
                        $last = $last + 1;
                        if($page != 1){
                            $keyboardone = [];
                            $keyboardone['text'] = '⬅️上一页';
                            $keyboardone['callback_data'] = 'buygoods'.$goodsRid.'_'.($page-1);
                            $keyboard[$last][] = $keyboardone;
                        }
                        
                        if($page < $totalpage){
                            $keyboardone = [];
                            $keyboardone['text'] = '➡️下一页';
                            $keyboardone['callback_data'] = 'buygoods'.$goodsRid.'_'.($page+1);
                            $keyboard[$last][] = $keyboardone;
                        }
                    }

                    $reply_markup = [
                        'inline_keyboard' => $keyboard
                    ];
                    $encodedKeyboard = json_encode($reply_markup);
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
            
            //购买监控套餐
            }elseif(in_array($message,['buywalletmonitor5','buywalletmonitor10','buywalletmonitor20','buywalletmonitor50','buywalletmonitor100','buywalletmonitor200']) && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '购买监控套餐请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }

                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = "用户信息为空，请发送 /start 初始化用户，然后再购买监控套餐";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    //判断余额
                    $monitorBot = MonitorBot::where('bot_rid',$bot_rid)->where('status',0)->first();
                    if(empty($monitorBot)){
                        $replytext = "对应无监控套餐,请联系客服！";
                        
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                        $encodedKeyboard = json_encode($keyboard);
                    }else{
                        $kou = 0;
                        $addwallet = 0;
                        switch($message){
                            case 'buywalletmonitor5':
                                if($monitorBot->price_usdt_5 > 0 && floatval($botuser->cash_usdt) >= floatval($monitorBot->price_usdt_5)){
                                    $kou = $monitorBot->price_usdt_5;
                                    $addwallet = 5;
                                }
                                break;
                            case 'buywalletmonitor10':
                                if($monitorBot->price_usdt_10 > 0 && floatval($botuser->cash_usdt) >= floatval($monitorBot->price_usdt_10)){
                                    $kou = $monitorBot->price_usdt_10;
                                    $addwallet = 10;
                                }
                                break;
                            case 'buywalletmonitor20':
                                if($monitorBot->price_usdt_20 > 0 && floatval($botuser->cash_usdt) >= floatval($monitorBot->price_usdt_20)){
                                    $kou = $monitorBot->price_usdt_20;
                                    $addwallet = 20;
                                }
                                break;
                            case 'buywalletmonitor50':
                                if($monitorBot->price_usdt_50 > 0 && floatval($botuser->cash_usdt) >= floatval($monitorBot->price_usdt_50)){
                                    $kou = $monitorBot->price_usdt_50;
                                    $addwallet = 50;
                                }
                                break;
                            case 'buywalletmonitor100':
                                if($monitorBot->price_usdt_100 > 0 && floatval($botuser->cash_usdt) >= floatval($monitorBot->price_usdt_100)){
                                    $kou = $monitorBot->price_usdt_100;
                                    $addwallet = 100;
                                }
                                break;
                            case 'buywalletmonitor200':
                                if($monitorBot->price_usdt_200 > 0 && floatval($botuser->cash_usdt) >= floatval($monitorBot->price_usdt_200)){
                                    $kou = $monitorBot->price_usdt_200;
                                    $addwallet = 200;
                                }
                                break;
                            default:
                                // code...
                                break;
                        }
                        if(empty($kou)){
                            $replytext = "USDT余额不足，请充值USDT！";
                            //内联按钮
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '10 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt10'],
                                        ['text' => '100 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt100'],
                                        ['text' => '500 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt500']
                                    ],
                                    [
                                        ['text' => '1000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt1000'],
                                        ['text' => '2000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt2000'],
                                        ['text' => '5000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt5000']
                                    ],
                                    [
                                        ['text' => '⬅️返回上一步', 'callback_data' => 'monitorwallet_1'],
                                        ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                    ]
                                ]
                            ];
                            $encodedKeyboard = json_encode($keyboard);
                        }else{
                            $replytext = "恭喜您！监控套餐购买成功！\n"
                                        ."扣除USDT：".$kou."\n"
                                        ."增加监控数量：".$addwallet;
                            TelegramBotUser::where('rid',$botuser->rid)->update(['cash_usdt' => $botuser->cash_usdt - $kou,'max_monitor_wallet' => $botuser->max_monitor_wallet + $addwallet]);
                            //内联按钮
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '⬅️返回上一步', 'callback_data' => 'monitorwallet_1'],
                                        ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                    ]
                                ]
                            ];
                            $encodedKeyboard = json_encode($keyboard);
                        }
                    }
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);

                return '';

            //点击能量的智能托管-充值trx充值usdt
            }elseif(in_array($message,['aitrusteeshiprechargetrx10','aitrusteeshiprechargetrx100','aitrusteeshiprechargetrx500','aitrusteeshiprechargetrx1000','aitrusteeshiprechargetrx2000','aitrusteeshiprechargetrx5000','aitrusteeshiprechargeusdt10','aitrusteeshiprechargeusdt100','aitrusteeshiprechargeusdt500','aitrusteeshiprechargeusdt1000','aitrusteeshiprechargeusdt2000','aitrusteeshiprechargeusdt5000']) && $inlinecall == 'Y'){
                //该命令只能私聊机器人
                if(mb_substr($chatid,0,1) == '-'){
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => '能量智能托管请私聊机器人！', 
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $replytext = "开始购买";
                
                //内联按钮
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '10 TRX', 'callback_data' => 'aitrusteeshiprechargetrx10'],
                            ['text' => '100 TRX', 'callback_data' => 'aitrusteeshiprechargetrx100'],
                            ['text' => '500 TRX', 'callback_data' => 'aitrusteeshiprechargetrx500']
                        ],
                        [
                            ['text' => '1000 TRX', 'callback_data' => 'aitrusteeshiprechargetrx1000'],
                            ['text' => '2000 TRX', 'callback_data' => 'aitrusteeshiprechargetrx2000'],
                            ['text' => '5000 TRX', 'callback_data' => 'aitrusteeshiprechargetrx5000']
                        ],
                        [
                            ['text' => '10 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt10'],
                            ['text' => '100 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt100'],
                            ['text' => '500 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt500']
                        ],
                        [
                            ['text' => '1000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt1000'],
                            ['text' => '2000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt2000'],
                            ['text' => '5000 USDT', 'callback_data' => 'aitrusteeshiprechargeusdt5000']
                        ],
                        [
                            ['text' => '返回上一步', 'callback_data' => 'aitrusteeship']
                        ]
                    ]
                ];
                $encodedKeyboard = json_encode($keyboard);
                
                //如果当前有订单未完成,则需要提示先取消订单
                $isCan = FmsRechargeOrder::where('bot_rid',$bot_rid)->where('status',0)->where('recharge_tg_uid',$chatid)->first();
                if(!empty($isCan)){
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '取消未充值的订单', 'callback_data' => 'cancelaitrusteeshiprechargeunpaid']
                            ]
                        ]
                    ];
                    $reply_markup = json_encode($keyboard);
                
                    $replytext = "您还有未完成的充值订单，暂时无法继续提交。如果需要重新提交，请点击下方取消未完成的充值订单\n"
                                ."<b>充值金额：</b>".preg_replace('/\.?0+$/', '', $isCan->recharge_pay_price) ." ".$isCan->recharge_coin_name."\n"
                                ."<b>应支付金额：</b><code>". preg_replace('/\.?0+$/', '', $isCan->need_pay_price) ." ".$isCan->recharge_coin_name."</code>\n"
                                ."<b>过期时间：</b>".$isCan->expire_time;
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                    return '';
                }
                
                //查充值钱包地址
                $telegrambot = TelegramBot::where('rid',$bot_rid)->first();
                if(empty($telegrambot) || empty($telegrambot->recharge_wallet_addr)){
                    $replytext = "暂未查询到可用充值钱包，请联系客服配置！";
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                
                $expireTime = date('Y-m-d H:i:s',strtotime('+15 minutes', strtotime('now')));
                //生成随机金额,如果金额对应有未完成的订单,就继续生成
                $randomPrice = 'N';
                while($randomPrice == 'N'){
                    $recharge_coin_name = strpos($message,'trx') ?'trx':'usdt';
                    $recharge_pay_price = str_replace(['aitrusteeshiprechargeusdt','aitrusteeshiprechargetrx'],'',$message);
                    $need_pay_price  = (mt_rand(1, 99) / 100) + $recharge_pay_price;
                    
                    $isCanPrice = FmsRechargeOrder::where('status',0)->where('need_pay_price',$need_pay_price)->first();
                    if(empty($isCanPrice)){
                        $randomPrice = 'Y';
                        
                        $insert_data = [];
                        $insert_data['bot_rid'] = $bot_rid;
                        $insert_data['recharge_tg_uid'] = $chatid;
                        $insert_data['recharge_tg_username'] = $request->callback_query['from']['username'] ?? '';
                        $insert_data['recharge_coin_name'] = $recharge_coin_name;
                        $insert_data['recharge_pay_price'] = $recharge_pay_price;
                        $insert_data['need_pay_price'] = $need_pay_price;
                        $insert_data['status'] = 0;	
                        $insert_data['create_time'] = nowDate();
                        $insert_data['expire_time'] = $expireTime; 
                         
                        $recharge_order_rid = FmsRechargeOrder::insertGetId($insert_data);
                    }
                }
                
                $replytext = "<b>下单成功，请核对金额和地址充值</b>\n"
                            ."提示：充值后,将会在10秒内入账！\n"
                            ."充值订单：".$recharge_order_rid."\n"
                            ."➖➖➖➖➖➖➖➖\n"
                            ."<b>🟢支付金额：</b><code>".preg_replace('/\.?0+$/', '', $need_pay_price)."</code> <b>".$recharge_coin_name."</b> (点击金额复制)\n"
                            ."<b>🟢支付地址：</b><code>".$telegrambot->recharge_wallet_addr."</code>\n"
                            ."<b>🟢失效时间：".$expireTime."</b>\n"
                            ."➖➖➖➖➖➖➖➖\n"
                            ."⚠️<b>请在订单失效时间支付，订单过期后请重新发起！</b>\n"
                            ."⚠️<b>点击地址和金额复制，金额或者地址错误将无法追回！</b>\n"
                            ."当前时间：".nowDate();
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
                
            //取消完成的充值订单
            }elseif($message == 'cancelaitrusteeshiprechargeunpaid' && $inlinecall == 'Y'){
                $save_data = [];
                $save_data['status'] = 3;      //会员取消
                $save_data['cancel_time'] = nowDate();      
                FmsRechargeOrder::where('bot_rid',$bot_rid)->where('status',0)->where('recharge_tg_uid',$chatid)->update($save_data);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => '您的未完成充值订单已取消，可重新发起充值！', 
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                return '';
                
            //出售会员的命令
            }elseif(mb_substr($message,0,8) == 'premium_' && mb_strlen($message) == 40 && $inlinecall == 'Y'){
                $username = $request->callback_query['from']['username'] ?? '';
                
                $packageData = PremiumPlatformPackage::from('t_premium_platform_package as a')
                            ->join('t_premium_platform as b','a.premium_platform_rid','b.rid')
                            ->where('a.callback_data', $message)
                            ->where('a.status',0)
                            ->where('b.status',0)
                            ->select('a.package_name','a.package_month','a.usdt_price','a.show_notes','b.receive_wallet','a.rid')
                            ->first();
                
                if(empty($packageData)){
                    $replytext = "@".$username." <b>您好！该套餐已暂停购买，请选择其他套餐！</b>\n";
                }else{
                    #设置缓存
                    $account_goumai = getRedis('buypremium'.$chatid);
                    if(!empty($account_goumai)){
                        deleteRedis('buypremium'.$chatid);
                    }
                    setexRedis('buypremium'.$chatid,$packageData->rid,900);
                    
                    $replytext = "<b>您好！请直接发送需要开通/续费会员的Telegram用户名：</b>\n"
                            ."<b>提示：用户名以@开头，如</b> @".$username."\n"
                            .$packageData->show_notes."\n"
                            ."➖➖➖➖➖➖➖➖\n"
                            ."<b>🟢当前套餐：".$packageData->package_name."</b>\n"
                            ."<b>🟢开通月份：</b>".$packageData->package_month."\n"
                            ."➖➖➖➖➖➖➖➖\n"
                            ."⚠️<b>点击地址复制，点击金额复制，金额或者地址错误将无法追回！</b>";
                };
                
                #查询会员放入
                $keyboardList = PremiumPlatformPackage::from('t_premium_platform_package as a')
                            ->join('t_premium_platform as b','a.premium_platform_rid','b.rid')
                            ->where('a.bot_rid', $bot_rid)
                            ->where('a.status', 0)
                            ->where('b.status', 0)
                            ->select('a.package_name as keyboard_name','a.callback_data as keyboard_value')
                            ->orderBy('a.seq_sn','desc')
                            ->get();
            
                //有键盘的时候显示
                if($keyboardList->count() > 0){
                    $keyboardone = [];
                    $keyboard = [];
                    $s = 0;
                    
                    $keyboardone['text'] = "点击 ↓ 重新选择套餐(点我查看说明)";
                    $keyboardone['callback_data'] = "重新选择套餐premium";
                    $keyboard[0][] = $keyboardone;
                    $keyboardone = [];
                    $diyihang = 'Y';
                    
                    foreach ($keyboardList as $k => $v) {
                        //内联按钮
                        $keyboardone['text'] = $v->keyboard_name;
                        $keyboardone['callback_data'] = $v->keyboard_value;
                        
                        if(!empty($keyboard)){
                            if(count($keyboard[$s]) == 2 || $diyihang == 'Y'){
                                $s++;
                                $diyihang = 'N';
                            }
                        }
                        
                        $keyboard[$s][] = $keyboardone;
                        $keyboardone = [];
                    }
                    
                    $reply_markup = [
                        'inline_keyboard' => $keyboard
                    ];
                    $reply_markup = json_encode($reply_markup);
                //没有套餐的时候隐藏键盘
                }else{
                    $replytext = $replytext."😭😭😭<b>暂无可用会员套餐，请联系管理员！</b>";
                    
                    $reply_markup = $telegram->replyKeyboardHide([
                        'keyboard' => [], 
                        'resize_keyboard' => true,  //设置为true键盘不会那么高
                        'one_time_keyboard' => false
                    ]);
                }
                
                // $reply_markup = Keyboard::forceReply(['force_reply'=>true,'input_field_placeholder'=>"请输入需要开通会员的用户名，比例 @".$username]);
                
                #发送图片
                if(!empty($packageData->package_pic)){
                    $response = $telegram->sendPhoto([
                        'chat_id' => $chatid, 
                        'photo' => InputFile::create($packageData->package_pic, 'demo'),
                        'caption' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                }else{
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $reply_markup
                    ]);
                }
                return '';
                
            //点击会员的重新选择套餐
            }elseif($message == '重新选择套餐premium' && $inlinecall == 'Y'){
                $replytext = "开通telegram会员（⚠️务必仔细阅读⚠️）\n"
                            ."🔴请确认地址是否正确\n"
                            ."🔴请确认金额完全无误\n"
                            ."🔴请确认开通的telegram账号无误\n";
                            
                //调用官方方法
                $param = [
                    'callback_query_id' => $request->callback_query['id'], 
                    'text' => $replytext,
                    'show_alert' => true
                ];
                
                $urlString = "https://api.telegram.org/bot".$data->bot_token."/answerCallbackQuery";
                
                $response = post_multi($urlString,$param);
                return '';
                
            //点击会员的取消未完成的订单
            }elseif($message == 'cancelpremiumunpaid' && $inlinecall == 'Y'){
                $save_data = [];
                $save_data['status'] = 4;      //会员取消
                $save_data['cancel_time'] = nowDate();      
                PremiumPlatformOrder::where('bot_rid',$bot_rid)->where('status',0)->where('buy_tg_uid',$chatid)->update($save_data);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => '您的未完成会员订单已取消，可重新发起购买会员！', 
                    'parse_mode' => 'MarkDown',
                    'allow_sending_without_reply' => true
                ]);
                return '';
                
            //会员回复开通会员的账号,匹配@开头,大于等于4位数
            }elseif(mb_substr($message,0,1) == '@' && mb_strlen($message) >= 4){
                $premium_package_rid = getRedis('buypremium'.$chatid);
                $username = $result['message']['from']['username'] ?? '';
                if($premium_package_rid){
                    //该命令只能私聊机器人
                    if(mb_substr($chatid,0,1) == '-'){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => '该命令只能私聊机器人！', 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                    //如果当前有订单未完成,则需要提示先取消订单
                    $isCan = PremiumPlatformOrder::where('bot_rid',$bot_rid)->whereIn('status',[0,1])->where('buy_tg_uid',$chatid)->first();
                    if(!empty($isCan)){
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '取消未完成的订单', 'callback_data' => 'cancelpremiumunpaid']
                                ]
                            ]
                        ];
                        $reply_markup = json_encode($keyboard);
                    
                        $replytext = "您还有未完成的会员订单，暂时无法继续提交。如果需要重新提交，请点击下方取消未完成的会员订单";
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $reply_markup
                        ]);
                        return '';
                    }
                    
                    $packageData = PremiumPlatformPackage::from('t_premium_platform_package as a')
                                ->join('t_premium_platform as b','a.premium_platform_rid','b.rid')
                                ->where('a.rid', $premium_package_rid)
                                ->where('a.status',0)
                                ->where('b.status',0)
                                ->select('a.package_name','a.package_month','a.usdt_price','a.show_notes','b.receive_wallet','a.rid','b.platform_hash','b.platform_cookie','a.premium_platform_rid')
                                ->first();
                    
                    if(empty($packageData)){
                        $replytext = "@".$username." <b>您好！该套餐已暂停购买，请选择其他套餐！</b>\n";
                    }else{
                        $premiumMonth = $packageData->package_month;
                        $hash = $packageData->platform_hash;
                        $rsa_services = new RsaServices();
                        $cookie = $rsa_services->privateDecrypt($packageData->platform_cookie);
                        
                        #第一步 获取被赠送用户的会员信息
                        $user = curl_post_https("https://fragment.com/api?hash=".$hash,"query=".$message."&months=".$premiumMonth."&method=searchPremiumGiftRecipient",null,$cookie);
                        $json = json_decode($user,true);
                        
                        if(empty($json['ok'])){
                            $error = isset($json) ?(isset($json['error']) ?$json['error']:'未知'):'未知';
                            $replytext = "获取用户信息失败，请检查用户名是否正确，例如 @aaaa\n"
                                        ."错误信息：".$error;
                        }else{
                            $premiumUser = $json['found']['name']??"未知";
                            $expireTime = date('Y-m-d H:i:s',strtotime('+15 minutes', strtotime('now')));
                            //生成随机金额,如果金额对应有未完成的订单,就继续生成
                            $randomPrice = 'N';
                            while($randomPrice == 'N'){
                                $payUsdtPrice = (mt_rand(100, 200) / 10000) + $packageData->usdt_price;
                                
                                $isCanPrice = PremiumPlatformOrder::whereIn('status',[0,1])->where('need_pay_usdt',$payUsdtPrice)->first();
                                if(empty($isCanPrice)){
                                    $randomPrice = 'Y';
                                    
                                    $insert_data = [];
                                    $insert_data['bot_rid'] = $bot_rid;
                                    $insert_data['premium_platform_rid'] = $packageData->premium_platform_rid;
                                    $insert_data['source_type'] = 2;
                                    $insert_data['buy_tg_uid'] = $chatid;
                                    $insert_data['buy_tg_username'] = $username;
                                    $insert_data['premium_tg_username'] = $message;
                                    $insert_data['need_pay_usdt'] = $payUsdtPrice;
                                    $insert_data['status'] = 0;	
                                    $insert_data['premium_platform_package_rid'] = $packageData->rid;
                                    $insert_data['premium_package_month'] = $premiumMonth; 
                                    $insert_data['create_time'] = nowDate();
                                    $insert_data['expire_time'] = $expireTime; 
                                    $insert_data['recipient'] = $json['found']['recipient']; //获得用户唯一标识 第2步需要使用
                                     
                                    $premiun_order_rid = PremiumPlatformOrder::insertGetId($insert_data);
                                }
                            }
                            
                            //删除缓存只下单一次
                            deleteRedis('buypremium'.$chatid);
                            
                            $replytext = "<b>下单成功，请核对金额和地址支付</b>\n"
                                        ."开通用户：".$message."\n"
                                        ."用户昵称：".$premiumUser."\n"
                                        ."订单号：".$premiun_order_rid."\n"
                                        ."➖➖➖➖➖➖➖➖\n"
                                        ."<b>🟢当前套餐：".$packageData->package_name."</b>\n"
                                        ."<b>🟢开通月份：</b>".$packageData->package_month."\n"
                                        ."<b>🟢支付金额：</b><code>".$payUsdtPrice."</code> <b>USDT</b> (点击金额复制)\n"
                                        ."<b>🟢支付地址：</b><code>".$packageData->receive_wallet."</code>\n"
                                        ."<b>🟢失效时间：".$expireTime."</b>\n"
                                        ."➖➖➖➖➖➖➖➖\n"
                                        ."⚠️<b>请在订单失效时间支付，订单过期后请重新发起！</b>\n"
                                        ."⚠️<b>点击地址和金额复制，金额或者地址错误将无法追回！</b>\n"
                                        ."⏬<b>点击 余额支付，可直接使用充值的USDT余额支付</b>\n"
                                        ."当前时间：".nowDate();
                            //内联按钮
                            $keyboard = [
                                'inline_keyboard' => [
                                    [
                                        ['text' => '余额支付', 'callback_data' => 'tgpremiumwalletpay_'.$premiun_order_rid]
                                    ],
                                    [
                                        ['text' => '我要充值', 'callback_data' => 'aitrusteeshiprechargetrx']    
                                    ]
                                ]
                            ];
                            $encodedKeyboard = json_encode($keyboard);
                            
                            $response = $telegram->sendMessage([
                                'chat_id' => $chatid, 
                                'text' => $replytext, 
                                'reply_to_message_id' => $result['message']['message_id'],
                                'parse_mode' => 'HTML',
                                'allow_sending_without_reply' => true,
                                'reply_markup' => $encodedKeyboard
                            ]);
                            return '';
                        }
                    }
                    
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
            
            //开通会员余额支付
            }elseif(mb_substr($message,0,18) == 'tgpremiumwalletpay'){
                $orderId = str_replace(['tgpremiumwalletpay_'],'',$message);
                $premiumOrder = PremiumPlatformOrder::where('rid',$orderId)->where('buy_tg_uid',$chatid)->where('bot_rid',$bot_rid)->first();
                
                if(empty($premiumOrder) || $premiumOrder->status != 0){
                    $replytext = '订单不存在或者不是待支付状态';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['callback_query']['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                    
                }else{
                    $botUser = TelegramBotUser::where('tg_uid',$chatid)->where('bot_rid',$bot_rid)->first();
                    
                    if(empty($botUser) || $premiumOrder->need_pay_usdt > $botUser->cash_usdt){
                        $replytext = '用户USDT余额不足';
                                
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'reply_to_message_id' => $result['callback_query']['message']['message_id'],
                            'parse_mode' => 'MarkDown',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }else{
                        $botUser->decrement('cash_usdt',$premiumOrder->need_pay_usdt);
                        $premiumOrder->status = 1;
                        $premiumOrder->update_time = nowDate();
                        $premiumOrder->save();
                        
                        $replytext = "会员订单支付成功，开通中···\n"
                                    ."开通用户：".$premiumOrder->premium_tg_username."\n"
                                    ."开通时间：".$premiumOrder->premium_package_month;
                        
                        $response = $telegram->editMessageText([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'message_id' => $result['callback_query']['message']['message_id'],
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true
                        ]);
                        return '';
                    }
                }

            //查授权命令
            }elseif(mb_substr($message,0,3) == '查授权'){
                $message = trim(str_replace(['查授权'],'',$message)); 
                $message = preg_split('/\s+/', $message, -1, PREG_SPLIT_NO_EMPTY);
                
                if(!isset($message) || empty($message)){
                    $replytext = '输入格式错误，请输入格式：查授权xxxxxxx  xxxxxxx为波场钱包地址。'.PHP_EOL.PHP_EOL
                                .'比如  查授权TYASr5UV6HEcXatwdFQfmLVUqQQQMUxHLS';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                    return '';
                }
                if(mb_substr($message[0],0,1) == 'T' && mb_strlen($message[0]) == 34){
                    $replytext = $this->checkapprove($message,$bot_rid,$chatid);
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '波场链上查询', 'url' => 'https://tronscan.org/#/address/'.$message[0]]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                    if(isset($result['message']['message_id'])){
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                    }else{
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $encodedKeyboard
                        ]);
                    }
                    
                }else{
                    $replytext = '输入格式错误，请输入格式：查授权xxxxxxx  xxxxxxx为波场钱包地址。'.PHP_EOL.PHP_EOL
                                .'比如  查授权TYASr5UV6HEcXatwdFQfmLVUqQQQMUxHLS';
                                
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'reply_to_message_id' => $result['message']['message_id'],
                        'parse_mode' => 'MarkDown',
                        'allow_sending_without_reply' => true
                    ]);
                }
                return '';
            };
            
            //回复汇率
            if(preg_match('/^[0-9]*\s*+(usdt|u)$/i', $message)){
                $message = preg_replace('/\s*+(usdt|u)/i','',$message);
                if(empty($message)){
                    $message = 1;
                }
                
                //替换变量
                $walletcoin = TransitWalletCoin::from('t_transit_wallet_coin as a')
                            ->join('t_transit_wallet as b','a.transit_wallet_id','b.rid')
                            ->where('b.bot_rid', $bot_rid)
                            ->where('in_coin_name','usdt')
                            ->where('out_coin_name','trx')
                            ->where('b.status',0)
                            ->select('a.exchange_rate','b.receive_wallet')
                            ->first();
                
                if(empty($walletcoin)){
                    $replytext = "请先配置机器人闪兑钱包并开启，设置汇率！！";
                }else{
                    $replytext = "💹实时汇率: <b>".$message ." USDT = ". round($message * $walletcoin->exchange_rate,2) ." TRX</b>".PHP_EOL.PHP_EOL
                            ."官方USDT-TRC20自动兑换地址（点击地址复制）".PHP_EOL
                            ."<code>".$walletcoin->receive_wallet."</code>";
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true
                ]);
                
                return '';
            };
            
            //判断是否计算
            $messagepd = str_replace([' '],'',$message);
            if((strpos($message, '+') !== false || strpos($message, '-') !== false || strpos($message, '*') !== false || strpos($message, '/') !== false) && preg_match('/^((\d++(\.\d+)?|\((?1)\))((\+|\/|\*|-)(\d++(\.\d+)?|(?1)))*)$/',$messagepd)) {
                try {
                    $expressionLanguage = new ExpressionLanguage();
                    $replytext = $message." = ".$expressionLanguage->evaluate($messagepd);
                } catch (\Throwable $e) {
                    $replytext = "计算表达式错误：" . $e->getMessage();
                }
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'allow_sending_without_reply' => true,
                    'parse_mode' => 'HTML'
                ]);
                return '';
            }  
            
            #判断消息和回复内容（PostgreSQL 兼容：使用 LIKE 匹配逗号分隔的关键字）
            // 首先使用原始消息（包含 emoji）进行匹配
            $escapedOriginalMessage = str_replace("'", "''", $originalMessage); // 转义单引号防止 SQL 注入
            $keyreply = TelegramBotKeyreply::where('bot_rid', $bot_rid)
                ->where('status', 0)
                ->whereRaw("(',' || monitor_word || ',' LIKE ?)", [",%,{$escapedOriginalMessage},%"])
                ->first();
            
            // 如果第一次匹配失败，尝试使用清理后的消息（移除特殊字符）再次匹配
            if(empty($keyreply) && isset($cleanedMessage) && $cleanedMessage != $originalMessage){
                $escapedCleanedMessage = str_replace("'", "''", $cleanedMessage);
                $keyreply = TelegramBotKeyreply::where('bot_rid', $bot_rid)
                    ->where('status', 0)
                    ->whereRaw("(',' || monitor_word || ',' LIKE ?)", [",%,{$escapedCleanedMessage},%"])
                    ->first();
            }
            
            // 如果还是失败，尝试使用过滤 emoji/符号 后的纯文本匹配（作为最后尝试）
            // 解决 "💹闪兑TRX"、"❇️智能托管"、"🔠购买靓号" 等带 emoji 的关键词无法匹配的问题
            if(empty($keyreply)){
                $filteredMessage = $this->filterTextForKeywordMatch($originalMessage);
                if(!empty($filteredMessage)){
                    $escapedFilteredMessage = str_replace("'", "''", $filteredMessage);
                    $keyreply = TelegramBotKeyreply::where('bot_rid', $bot_rid)
                        ->where('status', 0)
                        ->whereRaw("(',' || monitor_word || ',' LIKE ?)", [",%,{$escapedFilteredMessage},%"])
                        ->orderBy('rid')
                        ->first();
                    // 若 SQL LIKE 仍失败（如编码差异），尝试 PHP 逐条匹配
                    if(empty($keyreply)){
                        $allKeyreplies = TelegramBotKeyreply::where('bot_rid', $bot_rid)->where('status', 0)->orderBy('rid')->get();
                        foreach($allKeyreplies as $kr){
                            $keywords = array_map('trim', explode(',', $kr->monitor_word ?? ''));
                            if(in_array($filteredMessage, $keywords)){
                                $keyreply = $kr;
                                break;
                            }
                        }
                    }
                }
            }
            
            \Log::info('Telegram 关键字匹配', [
                'bot_rid' => $bot_rid,
                'original_message' => $originalMessage ?? $message,
                'cleaned_message' => $cleanedMessage ?? '',
                'filtered_message' => $message ?? '',
                'keyreply_found' => !empty($keyreply),
                'keyreply_rid' => $keyreply->rid ?? null,
                'keyreply_opt_type' => $keyreply->opt_type ?? null,
            ]);
            
            // 调试：记录关键词匹配详情
            if(!empty($keyreply)){
                \Log::info('关键词匹配成功', [
                    'keyreply_rid' => $keyreply->rid,
                    'opt_type' => $keyreply->opt_type,
                    'reply_content_length' => strlen($keyreply->reply_content ?? ''),
                ]);
            }

        } catch (\Throwable $e) {
            llog($e);
            return '';
        }

        if(empty($keyreply)){
            return '';
        }
        
        //"1" => "回复消息(通用)", "2" => "回复ID", "3" => "回复消息(通用)+能量按钮", "4" => "回复消息(私聊)+会员按钮", "5" => "回复消息(私聊)+充值按钮", "6" => "回复消息(私聊)+监控按钮", "7" => "回复消息(私聊)+商品按钮", "8" => "回复消息(私聊)+个人中心"
        // 说明：
        // - opt_type 4（购买会员）允许在群里直接展示购买入口，提升转化，不再强制要求私聊
        // - opt_type 5,6,7,8 仍然只能在私聊里使用，群里点击时提示私聊机器人
        if(in_array($keyreply->opt_type,[5,6,7,8])){
            //该命令只能私聊机器人（不包含 opt_type 4）
            if(mb_substr($chatid,0,1) == '-'){
                //内联按钮
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '🤖私聊机器人', 'url' => 'https://t.me/'.$data->bot_username],
                            ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                        ]
                    ]
                ];
                $encodedKeyboard = json_encode($keyboard);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => "<b>该功能命令只能私聊机器人执行！点击下方私聊机器人</b> ！", 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                return '';
            }
        }
        
        //回复内容
        if(in_array($keyreply->opt_type,[1,3,4,5,6,7,8,9,10,11])){
            // opt_type 11 使用特殊处理，不使用数据库中的 reply_content，会在后面单独处理
            if($keyreply->opt_type == 11){
                // 跳过这里的处理，opt_type 11 会在后面单独处理
                $replytext = '';
                $replyphoto = '';
            } else {
                $replytext = $keyreply->reply_content;
                $replyphoto = $keyreply->reply_photo;
            }
            
            // 只有当 replytext 不为空时才处理变量替换
            if(!empty($replytext) && (strpos($replytext, 'trxusdtrate') !== false || strpos($replytext, 'trxusdtwallet') !== false || strpos($replytext, 'tgbotadmin') !== false || strpos($replytext, 'trxusdtshownotes') !== false || strpos($replytext, 'tgbotname') !== false || strpos($replytext, 'trx10usdtrate') !== false || strpos($replytext, 'trx100usdtrate') !== false || strpos($replytext, 'trx1000usdtrate') !== false)) {
                //替换变量
                $walletcoin = TransitWalletCoin::from('t_transit_wallet_coin as a')
                            ->join('t_transit_wallet as b','a.transit_wallet_id','b.rid')
                            ->where('b.bot_rid', $bot_rid)
                            ->where('in_coin_name','usdt')
                            ->where('out_coin_name','trx')
                            ->select('a.exchange_rate','b.receive_wallet','b.show_notes')
                            ->first();
                if(!empty($walletcoin) || $data->bot_admin_username || $data->bot_username){
                    $paraData = [
                        'trxusdtrate' => $walletcoin->exchange_rate ?? '',
                        'trxusdtwallet' => $walletcoin->receive_wallet ?? '',
                        'tgbotadmin' => $data->bot_admin_username ?? '',
                        'trxusdtshownotes' => $walletcoin->show_notes ?? '',
                        'tgbotname' => '@' . $data->bot_username ?? '',
                        'trx10usdtrate' => bcmul($walletcoin->exchange_rate ?? 0, 10, 2) + 0,
                        'trx100usdtrate' => bcmul($walletcoin->exchange_rate ?? 0, 100, 2) + 0,
                        'trx1000usdtrate' => bcmul($walletcoin->exchange_rate ?? 0, 1000, 2) + 0,
                    ];
                    
                    //检查参数是否匹配
                    preg_match_all('/\${.*?}/', $replytext, $matches);
                    $params = $matches[0];
                    $values = [];
                    foreach ($params as $param) {
                        $key = str_replace(['${', '}'], '', $param);
                        if(isset($paraData[$key])){
                            $values[$param] = $paraData[$key];
                        }
                    }
             
                    $replytext = strtr($replytext, $values);
                    //替换结束
                }
            }
            
            // 处理换行格式并标准化 HTML 格式
            // Telegram HTML 模式支持 \n 自动换行，不需要转换为 <br>
            // 统一换行符并确保 HTML 标签符合 Telegram HTML 规范
            if(!empty($replytext)){
                $replytext = $this->normalizeTelegramHtml($replytext);
            }
            $keyboardList = [];
            
            // opt_type 11 会在后面单独处理，这里跳过键盘处理
            // opt_type 4,5,6,7,8 会在下面单独处理并 return
            // 只有 opt_type 1,3,9,10 会到这里处理键盘
            if($keyreply->opt_type != 11){
                //回复内容时,查询关联键盘
                if($keyreply->opt_type == 1){
                #查询键盘,放入
                $keyboardList = TelegramBotKeyreplyKeyboard::from('t_telegram_bot_keyreply_keyboard as a')
                            ->join('t_telegram_bot_keyboard as b','a.keyboard_rid','b.rid')
                            ->where('a.bot_rid', $bot_rid)
                            ->where('a.keyreply_rid', $keyreply->rid)
                            ->where('b.status', 0)
                            ->select('b.keyboard_name','b.keyboard_type','b.inline_type','b.keyboard_value')
                            ->orderBy('b.seq_sn','desc')
                            ->get();
            //回复消息(通用)+能量按钮
            }elseif($keyreply->opt_type == 3){
                #查询能量放入
                $keyboardList = EnergyPlatformPackage::from('t_energy_platform_bot as b')
                            ->leftjoin('t_energy_platform_package as a', function($join)
                                {
                                    $join->on('b.bot_rid', '=', 'a.bot_rid')
                                         ->where('a.status', 0);
                                })
                            ->where('b.bot_rid', $bot_rid)
                            ->where('b.status', 0)
                            ->selectRaw('package_name as keyboard_name,2 as keyboard_type,2 as inline_type,callback_data as keyboard_value,is_open_ai_trusteeship')
                            ->orderBy('a.seq_sn','desc')
                            ->get();
            //回复消息(私聊)+会员按钮
            }elseif($keyreply->opt_type == 4){
                #设置缓存
                $account_goumai = getRedis('buypremium'.$chatid);
                if(!empty($account_goumai)){
                    deleteRedis('buypremium'.$chatid);
                }
                setexRedis('buypremium'.$chatid,'one',900);
                
                #查询会员放入 - 使用 DB::table() 避免 Eloquent 模型字段访问问题
                $keyboardList = DB::table('t_premium_platform_package as a')
                            ->join('t_premium_platform as b','a.premium_platform_rid','b.rid')
                            ->where('a.bot_rid', $bot_rid)
                            ->where('a.status', 0)
                            ->where('b.status', 0)
                            ->selectRaw('package_name as keyboard_name,2 as keyboard_type,2 as inline_type,callback_data as keyboard_value')
                            ->orderBy('a.seq_sn','desc')
                            ->get();
            //"回复消息(私聊)+充值按钮"
            }elseif($keyreply->opt_type == 5){
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = $replytext."用户信息为空，请发送 /start 初始化用户，然后再进行智能托管";
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $replytext = $replytext."\n\n<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."⚠️TRX余额不足时，不再执行智能托管，请及时充值\n"
                            ."⚠️充值的USDT可点击下方转换为TRX\n\n"
                            ."<b>请保证余额充足,点击下方可充值余额！</b>";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔥TRX充值', 'callback_data' => 'aitrusteeshiprechargetrx'],
                                ['text' => '🔥USDT充值', 'callback_data' => 'aitrusteeshiprechargeusdt'],
                                ['text' => '🔀U转TRX', 'callback_data' => 'aitrusteeshipusdtswaptrx']
                            ],
                            [
                                ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                            ],
                            [
                                ['text' => '👑我的托管地址', 'callback_data' => 'aitrusteeshipmyaddress']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }
                #发送图片
                if(!empty($replyphoto)){
                    $response = $telegram->sendPhoto([
                        'chat_id' => $chatid, 
                        'photo' => InputFile::create($replyphoto, 'demo'),
                        'caption' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }else{
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }
                
                return '';
                
            //回复消息(私聊)+监控按钮
            }elseif($keyreply->opt_type == 6){
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = $replytext."用户信息为空，请发送 /start 初始化用户，然后再进行钱包监控";
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $monitorWalletModel = MonitorWallet::where('bot_rid',$bot_rid)->where('tg_notice_obj',$chatid);
                    $count = $monitorWalletModel->count();
                    $offset = 0;
                    $totalpage = ceil($count / 10);
                    $monitorWallet = $monitorWalletModel->limit(10)->offset($offset)->orderBy('rid','desc')->get();
                    
                    $replytext = $replytext."\n\n<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."<b>剩余可监控数量：</b><code>".($botuser->max_monitor_wallet - $count)."</code>\n"
                            ."<b>可点击下方购买监控套餐！</b>\n"
                            ."当前已监控地址：".$count." 个\n";
                    
                    foreach ($monitorWallet as $k => $v) {
                        $replytext = $replytext."  <code>".$v->monitor_wallet. "</code>\n";
                    }
                    
                    //内联按钮
                    if($totalpage >= 2){
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '➕添加监控地址', 'callback_data' => 'monitorddaddress'],
                                    ['text' => '➖删除监控地址', 'callback_data' => 'monitordeleteaddress']
                                ],
                                [
                                    ['text' => '💎购买监控包', 'callback_data' => 'monitorwalletbuy'],
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ],
                                [
                                    ['text' => '➡️下一页', 'callback_data' => 'monitorwallet_2'],
                                    ['text' => '🛠修改备注', 'callback_data' => 'monitorwalletupdate_1'],
                                    ['text' => '🛠修改监控', 'callback_data' => 'monitorwalletfunc_1'],
                                ]
                            ]
                        ];
                    }else{
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '➕添加监控地址', 'callback_data' => 'monitorddaddress'],
                                    ['text' => '➖删除监控地址', 'callback_data' => 'monitordeleteaddress']
                                ],
                                [
                                    ['text' => '💎购买监控包', 'callback_data' => 'monitorwalletbuy'],
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ],
                                [
                                    ['text' => '🛠修改备注', 'callback_data' => 'monitorwalletupdate_1'],
                                    ['text' => '🛠修改监控', 'callback_data' => 'monitorwalletfunc_1'],
                                ]
                            ]
                        ];
                    }
                    $encodedKeyboard = json_encode($keyboard);
                }
                #发送图片
                if(!empty($replyphoto)){
                    $response = $telegram->sendPhoto([
                        'chat_id' => $chatid, 
                        'photo' => InputFile::create($replyphoto, 'demo'),
                        'caption' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }else{
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }
                
                return '';
            
            //回复消息(私聊)+商品按钮
            }elseif($keyreply->opt_type == 7){
                //查用户可监控地址的数量
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = $replytext."用户信息为空，请发送 /start 初始化用户，然后再进行购买";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $shopGoods = ShopGoodsBot::from('t_shop_goods_bot as a')
                                ->join('t_shop_goods as b','a.goods_rid','b.rid')
                                ->where('a.bot_rid',$bot_rid)
                                ->where('a.status',0)
                                ->where('b.status',0)
                                ->select('b.goods_name','b.rid')
                                ->orderBy('b.seq_sn','desc')
                                ->get();
                    
                    $replytext = $replytext."\n\n<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n";
                    
                    $keyboardone = [];
                    $keyboard = [];
                    $s = 0;
                    $add = 'N';
                    if($shopGoods->count() > 0){
                        $add = 'Y';
                        foreach ($shopGoods as $k => $v) {
                            //内联按钮
                            $keyboardone['text'] = $v->goods_name;
                            $keyboardone['callback_data'] = 'buygoods'.$v->rid.'_1';
                            
                            if(!empty($keyboard)){
                                if(count($keyboard[$s]) == 2){
                                    $s++;
                                }
                            }
                            
                            $keyboard[$s][] = $keyboardone;
                            $keyboardone = [];
                        }
                    }else{
                        $replytext = $replytext."<b>当前机器人无商品可购买！</b>";
                    }
                    
                    $last = $add == 'N'?0:($s + 1);
                    $keyboardone = [];
                    $keyboardone['text'] = '🔥我要充值';
                    $keyboardone['callback_data'] = 'aitrusteeshiprechargetrx';
                    $keyboard[$last][] = $keyboardone;
                    $keyboardone = [];
                    $keyboardone['text'] = '👨联系客服';
                    $keyboardone['url'] = 'https://t.me/'.mb_substr($data->bot_admin_username,1);
                    $keyboard[$last][] = $keyboardone;
                    
                    $reply_markup = [
                        'inline_keyboard' => $keyboard
                    ];
                    
                    $encodedKeyboard = json_encode($reply_markup);
                }
                #发送图片
                if(!empty($replyphoto)){
                    $response = $telegram->sendPhoto([
                        'chat_id' => $chatid, 
                        'photo' => InputFile::create($replyphoto, 'demo'),
                        'caption' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }else{
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }
                
                return '';

            //"回复消息(私聊)+个人中心"
            }elseif($keyreply->opt_type == 8){
                //查用户的余额
                $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                if(empty($botuser)){
                    $replytext = $replytext."用户信息为空，请发送 /start 初始化用户";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }else{
                    $replytext = $replytext."\n\n用户ID：<code>".$chatid."</code>\n"
                            ."用户名：@".($result['message']['from']['username'] ?? '')."\n"
                            ."<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                            ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                            ."⚠️充值的USDT可点击下方转换为TRX\n";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🔥TRX充值', 'callback_data' => 'aitrusteeshiprechargetrx'],
                                ['text' => '🔥USDT充值', 'callback_data' => 'aitrusteeshiprechargeusdt'],
                                ['text' => '🔀U转TRX', 'callback_data' => 'aitrusteeshipusdtswaptrx']
                            ]
                        ]
                    ];
                    $encodedKeyboard = json_encode($keyboard);
                }
                #发送图片
                if(!empty($replyphoto)){
                    $response = $telegram->sendPhoto([
                        'chat_id' => $chatid, 
                        'photo' => InputFile::create($replyphoto, 'demo'),
                        'caption' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }else{
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                }
                
                return '';
            
            //"回复消息(通用)+欧意汇率"
            }elseif($keyreply->opt_type == 9){
                $replytext = $this->queryokxc2c($message);
                
                if($message == 'z0' || $message == 'Z0'){
                    $selectallico = '✅';
                }else{
                    $selectallico = '';
                }
                
                if($message == 'z1' || $message == 'Z1'){
                    $selectbankico = '✅';
                }else{
                    $selectbankico = '';
                }
                
                if($message == 'z2' || $message == 'Z2'){
                    $selectalipayico = '✅';
                }else{
                    $selectalipayico = '';
                }
                
                if($message == 'z3' || $message == 'Z3'){
                    $selectwxico = '✅';
                }else{
                    $selectwxico = '';
                }
                
                //内联按钮
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => $selectallico.'所有', 'callback_data' => 'z0'],
                            ['text' => $selectbankico.'银行卡', 'callback_data' => 'z1'],
                            ['text' => $selectalipayico.'支付宝', 'callback_data' => 'z2'],
                            ['text' => $selectwxico.'微信', 'callback_data' => 'z3']
                        ]
                    ]
                ];
                $reply_markup = json_encode($keyboard);
                
                if($inlinecall == 'Y'){
                    $username = isset($request->callback_query['from']['username']) ?$request->callback_query['from']['username']:'-';
                    
                    $replytext = "@".$username."\n".$replytext;
                    
                    #发送图片
                    if(!empty($replyphoto)){
                        $response = $telegram->sendPhoto([
                            'chat_id' => $chatid, 
                            'photo' => InputFile::create($replyphoto, 'demo'),
                            'caption' => $replytext, 
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $reply_markup
                        ]);
                    }else{
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $reply_markup
                        ]);
                    }
                    
                }else{
                    #发送图片
                    if(!empty($replyphoto)){
                        $response = $telegram->sendPhoto([
                            'chat_id' => $chatid, 
                            'photo' => InputFile::create($replyphoto, 'demo'),
                            'caption' => $replytext, 
                            'reply_to_message_id' => $result['message']['message_id'],
                            'parse_mode' => 'HTML',
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $reply_markup
                        ]);
                        
                    }else{
                        $response = $telegram->sendMessage([
                            'chat_id' => $chatid, 
                            'text' => $replytext, 
                            'parse_mode' => 'HTML',
                            'reply_to_message_id' => $result['message']['message_id'],
                            'allow_sending_without_reply' => true,
                            'reply_markup' => $reply_markup
                        ]);
                    }   
                }
                
                return '';
            
            //"回复消息(通用)+能量按钮(笔数套餐)"
            }elseif($keyreply->opt_type == 10){
                $platformBot = EnergyPlatformBot::where("bot_rid",$bot_rid)->first();
                if(empty($platformBot)){
                    $replytext = "还未配置笔数模式，请联系客服配置后再使用！";
                }else{
                    $replytext = ($keyreply->reply_content == '--' ?'':$keyreply->reply_content)."\n\n👉<b>每笔单价：".$platformBot->per_bishu_usdt_price." USDT</b>\n"
                        // ."👉<b>每笔能量：".$platformBot->per_bishu_energy_quantity."</b>\n"
                        ."✅<b>支付地址：<code>".$platformBot->receive_wallet."</code></b>\n\n"
                        ."👆<b>请点击地址复制，直接转入USDT，如转入 ".($platformBot->per_bishu_usdt_price*100)." USDT，可获得100次免费转账次数</b>\n"
                        ."💰如已在机器人充值USDT，可发送指令：<u>添加笔数 Txx 10</u>，手工添加笔数，Txx为您的波场钱包地址，10为需要添加的笔数";
                        // ."<b>自动监控转入地址能量，不足".$platformBot->per_bishu_energy_quantity."时，自动补足能量</b>";
                }
                
                //群组不能使用
                if(mb_substr($chatid,0,1) == '-'){
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '⏳剩余笔数', 'url' => 'https://t.me/'.$data->bot_username],
                            ]
                        ]
                    ];
                }else{
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '⏳剩余笔数', 'callback_data' => 'energybishusy'],
                            ],
                            [
                                ['text' => '📣地址绑定通知', 'callback_data' => 'energybishubind'],
                                ['text' => '✏️已绑通知地址', 'callback_data' => 'energybishusearch']
                            ],
                            [
                                ['text' => '➕增加地址笔数', 'callback_data' => 'energybishubalanceadd']
                            ]
                        ]
                    ];
                }
                $encodedKeyboard = json_encode($keyboard);
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'text' => $replytext, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                    'reply_markup' => $encodedKeyboard
                ]);
                
                return '';
            }
            } // 结束 if($keyreply->opt_type != 11)

            // opt_type 11 (智能托管) 单独处理（在 if($keyreply->opt_type != 11) 块外）
            if($keyreply->opt_type == 11){
                \Log::info('处理 opt_type 11 (智能托管)', [
                    'bot_rid' => $bot_rid,
                    'chatid' => $chatid,
                    'is_group' => mb_substr($chatid,0,1) == '-',
                ]);
                
                //群组不能使用
                if(mb_substr($chatid,0,1) == '-'){
                    $replytext = "<b>能量智能托管说明</b>\n"
                                ."1️⃣在机器人充值TRX,每笔能量消耗该TRX余额\n"
                                ."2️⃣添加托管您的波场地址,地址一定要激活(转入TRX即可激活)\n"
                                ."3️⃣默认自动代理65000能量,可以给有USDT的地址免费转账一笔\n"
                                ."4️⃣可在我的托管地址中修改每笔代理能量数量和备注说明\n"
                                ."5️⃣每笔能量代理时间为一天,到期回收\n"
                                ."🔥点击下方私聊机器人赶紧使用吧！🔥";
                    
                    //内联按钮
                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)],
                                ['text' => '🤖私聊机器人', 'url' => 'https://t.me/'.$data->bot_username],
                            ]
                        ]
                    ];
                }else{
                    $botuser = TelegramBotUser::where('bot_rid',$bot_rid)->where('tg_uid',$chatid)->first();
                    if(empty($botuser)){
                        $replytext = "用户信息为空，请发送 /start 初始化用户";
                        
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '👨联系客服', 'url' => 'https://t.me/'.mb_substr($data->bot_admin_username,1)]
                                ]
                            ]
                        ];
                    }else{
                        $platformBot = EnergyPlatformBot::where("bot_rid",$bot_rid)->first();
                        if(isset($platformBot->trx_price_energy_32000) && isset($platformBot->trx_price_energy_65000)){
                            $replytext = "<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                                    ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                                    ."⚠️TRX余额不足时，不再执行智能托管，请及时充值\n"
                                    ."⚠️充值的USDT可点击下方转换为TRX\n\n"
                                    ."1️⃣在机器人充值TRX,每笔能量消耗该TRX余额\n"
                                    ."2️⃣添加托管您的波场地址,地址一定要激活(转入TRX即可激活)\n"
                                    ."3️⃣默认自动代理65000能量,可以给有USDT的地址免费转账一笔\n"
                                    ."4️⃣可在我的托管地址中修改每笔代理能量数量和备注说明\n"
                                    ."5️⃣每笔能量代理时间为一天,到期回收\n"
                                    ."<b>托管单价</b>：65000能量 <u>".$platformBot->trx_price_energy_32000." TRX</u>，131000能量 <u>".$platformBot->trx_price_energy_65000." TRX</u>\n"
                                    ."<b>请保证余额充足,点击下方可充值余额！</b>";
                        }else{
                            $replytext = "<b>TRX余额为：</b><code>".$botuser->cash_trx." TRX</code>\n"
                                ."<b>USDT余额为：</b><code>".$botuser->cash_usdt." USDT</code>\n\n"
                                ."⚠️TRX余额不足时，不再执行智能托管，请及时充值\n"
                                ."⚠️充值的USDT可点击下方转换为TRX\n\n"
                                ."1️⃣在机器人充值TRX,每笔能量消耗该TRX余额\n"
                                ."2️⃣添加托管您的波场地址,地址一定要激活(转入TRX即可激活)\n"
                                ."3️⃣默认自动代理65000能量,可以给有USDT的地址免费转账一笔\n"
                                ."4️⃣可在我的托管地址中修改每笔代理能量数量和备注说明\n"
                                ."5️⃣每笔能量代理时间为一天,到期回收\n"
                                ."<b>机器人未设置智能托管价格，请联系客服</b>\n"
                                ."<b>请保证余额充足,点击下方可充值余额！</b>";
                        }
                        
                        //内联按钮
                        $keyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '🔥TRX充值', 'callback_data' => 'aitrusteeshiprechargetrx'],
                                    ['text' => '🔥USDT充值', 'callback_data' => 'aitrusteeshiprechargeusdt'],
                                    ['text' => '🔀U转TRX', 'callback_data' => 'aitrusteeshipusdtswaptrx']
                                ],
                                [
                                    ['text' => '➕添加托管地址', 'callback_data' => 'aitrusteeshipaddaddress'],
                                    ['text' => '➖删除托管地址', 'callback_data' => 'aitrusteeshipdeleteaddress']
                                ],
                                [
                                    ['text' => '👑我的托管地址', 'callback_data' => 'aitrusteeshipmyaddress']
                                ]
                            ]
                        ];
                    }
                }
                
                // 标准化 HTML 格式
                if(!empty($replytext)){
                    $replytext = $this->normalizeTelegramHtml($replytext);
                }
                
                $encodedKeyboard = json_encode($keyboard);
                
                \Log::info('准备发送 opt_type 11 消息', [
                    'replytext_length' => strlen($replytext),
                    'has_keyboard' => !empty($keyboard),
                ]);
                
                try {
                    $response = $telegram->sendMessage([
                        'chat_id' => $chatid, 
                        'text' => $replytext, 
                        'parse_mode' => 'HTML',
                        'allow_sending_without_reply' => true,
                        'reply_markup' => $encodedKeyboard
                    ]);
                    
                    \Log::info('opt_type 11 消息发送成功', [
                        'response_id' => $response->getMessageId() ?? null,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('opt_type 11 消息发送失败', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
                
                return '';
            }

            // 对于 opt_type 1,3,4,9,10 等需要键盘的情况，继续处理
            // opt_type 5,6,7,8 已经在前面处理并 return，不会到这里
            // opt_type 4（购买会员）允许在群里使用，需要继续处理键盘和发送消息
            if(in_array($keyreply->opt_type,[1,3,4,9,10]) && !empty($replytext)){
            $keyboard = [];
            $replyKeyboard = []; // 快速回复键盘（ReplyKeyboard）
            
            //有键盘的时候显示
            if($keyboardList->count() > 0){
                $keyboardone = []; // 用于内联按钮的临时变量
                $keyboard = []; // 内联按钮数组
                $s = 0; // 内联按钮行索引
                
                // 获取第一个项的类型，用于判断是快速回复键盘还是内联按钮
                $firstItem = !empty($keyboardList) ? (is_array($keyboardList[0]) ? $keyboardList[0] : (array)$keyboardList[0]) : [];
                if(is_object($keyboardList[0]) && method_exists($keyboardList[0], 'toArray')){
                    $firstItem = $keyboardList[0]->toArray();
                }elseif(is_object($keyboardList[0]) && method_exists($keyboardList[0], 'getAttributes')){
                    $firstItem = array_merge($keyboardList[0]->getAttributes(), $keyboardList[0]->getOriginal());
                }
                
                // 判断键盘类型：1=快速回复键盘，2=内联按钮
                $isReplyKeyboard = !empty($firstItem) && isset($firstItem['keyboard_type']) && $firstItem['keyboard_type'] == 1;
                
                foreach ($keyboardList as $k => $v) {
                    // 转换为数组格式（如果是对象）
                    // 对于 Eloquent 模型，使用 toArray() 或 getAttributes() 来获取所有属性
                    if(is_object($v) && method_exists($v, 'toArray')){
                        $vArray = $v->toArray();
                    }elseif(is_object($v) && method_exists($v, 'getAttributes')){
                        $vArray = array_merge($v->getAttributes(), $v->getOriginal());
                    }else{
                        $vArray = is_array($v) ? $v : (array)$v;
                    }
                    
                    // 如果还是获取不到，尝试直接访问属性
                    if(empty($vArray['keyboard_name']) && is_object($v)){
                        $vArray['keyboard_name'] = $v->keyboard_name ?? $v->package_name ?? null;
                        $vArray['keyboard_type'] = $v->keyboard_type ?? null;
                        $vArray['inline_type'] = $v->inline_type ?? null;
                        $vArray['keyboard_value'] = $v->keyboard_value ?? $v->callback_data ?? null;
                    }
                    
                    //键盘（快速回复键盘）- keyboard_type = 1
                    if(isset($vArray['keyboard_type']) && $vArray['keyboard_type'] == 1 && !empty($vArray['keyboard_name'])){
                        // 快速回复键盘：每行最多 3 个按钮，按顺序添加
                        // 如果当前没有行，或者当前行已经有 3 个按钮，创建新行
                        if(empty($replyKeyboard) || count($replyKeyboard[count($replyKeyboard) - 1]) >= 3){
                            $replyKeyboard[] = [];
                        }
                        
                        // 添加按钮到当前最后一行
                        $currentRowIndex = count($replyKeyboard) - 1;
                        $replyKeyboard[$currentRowIndex][] = $vArray['keyboard_name'];
                        
                    //内联按钮 - keyboard_type = 2
                    }elseif(isset($vArray['keyboard_type']) && $vArray['keyboard_type'] == 2 && !empty($vArray['keyboard_name']) && !empty($vArray['keyboard_value'])){
                        //url
                        if(isset($vArray['inline_type']) && $vArray['inline_type'] == 1){
                            $keyboardone['text'] = $vArray['keyboard_name'];
                            $keyboardone['url'] = $vArray['keyboard_value'];
                            
                        //回调
                        }else{
                            $keyboardone['text'] = $vArray['keyboard_name'];
                            $keyboardone['callback_data'] = $vArray['keyboard_value'];
                        }
                        
                        if(!empty($keyboard)){
                            if(count($keyboard[$s]) == 2){
                                $s++;
                            }
                        }
                        
                        $keyboard[$s][] = $keyboardone;
                        $keyboardone = [];
                    }
                }
                
                //放入智能托管按钮（仅对内联按钮有效）
                if(!$isReplyKeyboard){
                    $isputaitrusteeship = 'N';
                    if(!empty($firstItem) && isset($firstItem['is_open_ai_trusteeship']) && $firstItem['is_open_ai_trusteeship'] == 'Y'){
                        //如果是群聊,则放入机器人地址
                        if(mb_substr($chatid,0,1) == '-'){
                            //内联按钮
                            $keyboardone['text'] = '❇️智能托管';
                            $keyboardone['url'] = 'https://t.me/'.$data->bot_username;
                        }else{
                            //内联按钮
                            $keyboardone['text'] = '❇️智能托管';
                            $keyboardone['callback_data'] = 'aitrusteeship';
                        }
                        $s = $s == 0?0:($s + 1);
                        $keyboard[$s][] = $keyboardone;
                        $isputaitrusteeship = 'Y';
                    }
                    
                    // is_open_bishu 字段不存在，注释掉相关逻辑
                    // if(isset($keyboardList[0]['is_open_bishu']) && $keyboardList[0]['is_open_bishu'] == 'Y'){
                    if(false){ // 临时禁用，因为 is_open_bishu 字段不存在
                        //如果是群聊,则放入机器人地址
                        // if(mb_substr($chatid,0,1) == '-'){
                        //     //内联按钮
                        //     $keyboardone['text'] = '🖌笔数套餐';
                        //     $keyboardone['url'] = 'https://t.me/'.$data->bot_username;
                        // }else{
                            //内联按钮
                            $keyboardone = [];
                            $keyboardone['text'] = '🖌笔数套餐';
                            $keyboardone['callback_data'] = 'energybishu';
                        // }
                        if($isputaitrusteeship == 'N'){
                            $s = $s == 0?0:($s + 1);
                        }
                        
                        $keyboard[$s][] = $keyboardone;
                    }
                }
                
                //键盘（检查 $keyboardList 是否为空）
                if($isReplyKeyboard && !empty($replyKeyboard)){
                    // 快速回复键盘（ReplyKeyboard）- keyboard_type = 1
                    // 构建键盘数组，每个子数组是一行按钮
                    $reply_markup = Keyboard::make([
                        'keyboard' => $replyKeyboard, 
                        'resize_keyboard' => true, 
                        'one_time_keyboard' => false,
                        'selective' => true
                    ]);
                    // 调试：记录快速回复键盘构建
                    \Log::info('构建快速回复键盘', [
                        'opt_type' => $keyreply->opt_type ?? null,
                        'keyboard_rows' => count($replyKeyboard),
                        'keyboard' => $replyKeyboard,
                    ]);
                //内联按钮
                }elseif(!empty($keyboard)){
                    // 有内联按钮
                    $reply_markup = [
                        'inline_keyboard' => $keyboard
                    ];
                    $reply_markup = json_encode($reply_markup);
                    // 调试：记录内联按钮构建
                    \Log::info('构建内联按钮', [
                        'opt_type' => $keyreply->opt_type ?? null,
                        'keyboard_count' => count($keyboard),
                        'keyboard' => $keyboard,
                    ]);
                }else{
                    // 没有键盘，不设置 reply_markup（让 Telegram 使用默认键盘）
                    $reply_markup = null;
                    // 调试：记录为什么没有键盘
                    \Log::info('未构建键盘', [
                        'opt_type' => $keyreply->opt_type ?? null,
                        'keyboard_empty' => empty($keyboard),
                        'replyKeyboard_empty' => empty($replyKeyboard),
                        'keyboardList_count' => $keyboardList->count() ?? 0,
                        'firstItem_keyboard_type' => $firstItem['keyboard_type'] ?? null,
                    ]);
                }
                
            //没有键盘
            }else{
                // 没有键盘，不设置 reply_markup（让 Telegram 使用默认键盘）
                $reply_markup = null;
                //键盘清空
                // $reply_markup = $telegram->replyKeyboardHide([
                //     'keyboard' => $keyboard, 
                //     'resize_keyboard' => true,  //设置为true键盘不会那么高
                //     'one_time_keyboard' => false
                // ]);
            }
            
            #异常处理
            try {
                // 调试：记录发送前的状态
                \Log::info('准备发送消息', [
                    'opt_type' => $keyreply->opt_type ?? null,
                    'replytext_empty' => empty($replytext),
                    'replytext_length' => strlen($replytext ?? ''),
                    'replyphoto_empty' => empty($replyphoto),
                    'has_reply_markup' => $reply_markup !== null,
                    'keyboard_count' => !empty($keyboardList) ? $keyboardList->count() : 0,
                ]);
                
                // 构建发送参数
                $sendParams = [
                    'chat_id' => $chatid, 
                    'parse_mode' => 'HTML',
                    'allow_sending_without_reply' => true,
                ];
                
                // 如果有 reply_markup，添加到参数中
                if($reply_markup !== null){
                    $sendParams['reply_markup'] = $reply_markup;
                }
                
                if($inlinecall == 'Y'){
                    #发送图片
                    if(!empty($replyphoto)){
                        $sendParams['photo'] = InputFile::create($replyphoto, 'demo');
                        $sendParams['caption'] = $replytext;
                        $response = $telegram->sendPhoto($sendParams);
                    }else{
                        $sendParams['text'] = $replytext;
                        $response = $telegram->sendMessage($sendParams);
                    }
                    
                }else{
                    #发送图片
                    if(!empty($replyphoto)){
                        $sendParams['photo'] = InputFile::create($replyphoto, 'demo');
                        $sendParams['caption'] = $replytext;
                        $sendParams['reply_to_message_id'] = $result['message']['message_id'];
                        $response = $telegram->sendPhoto($sendParams);
                    }else{
                        $sendParams['text'] = $replytext;
                        $sendParams['reply_to_message_id'] = $result['message']['message_id'];
                        $response = $telegram->sendMessage($sendParams);
                    }
                }
                
                // 调试：记录发送结果
                \Log::info('消息发送完成', [
                    'response_id' => $response->getMessageId() ?? null,
                    'success' => !empty($response),
                ]);
                
            } catch (\Throwable $e) {
                \Log::error('发送消息失败', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                return '';
            }
            } // 结束 if(in_array($keyreply->opt_type,[1,3,9,10]) && !empty($replytext))
        
        //查ID
        }elseif($keyreply->opt_type == 2){
            $replytext = "<b>用户ID：</b><code>".$result['message']['from']['id']."</code>\n"
                        ."用户名：@".($result['message']['from']['username'] ?? '')."\n"
                        ."用户昵称：<code>".($result['message']['from']['first_name'] ?? '').($result['message']['from']['last_name'] ?? '')."</code>\n\n";
                        
            if($result['message']['chat']['type'] == 'group' || $result['message']['chat']['type'] == 'supergroup'){
                $replytext = $replytext. "<b>群组ID：</b><code>".$chatid."</code>\n"
                            ."群组名：@".($result['message']['chat']['username'] ?? '')."\n"
                            ."群组昵称：<code>".$result['message']['chat']['title']."</code>\n"
                            ."群组类型：<code>".$result['message']['chat']['type']."</code>\n";
            }
            
            if(mb_substr($chatid,0,1) != '-'){
                $keyboard = [];
                $keyboardone = [['text'=>'查用户ID','request_users'=>['request_id'=>1,'user_is_bot'=>false]],
                                ['text'=>'查群组ID','request_chat'=>['request_id'=>2,'chat_is_channel'=>false]]
                               ];
                $keyboardtwo = [['text'=>'查机器人ID','request_users'=>['request_id'=>3,'user_is_bot'=>true]],
                                ['text'=>'查频道ID','request_chat'=>['request_id'=>4,'chat_is_channel'=>true]]
                               ];
                $keyboardthree = ['/start'];
                array_push($keyboard,$keyboardone);
                array_push($keyboard,$keyboardtwo);
                array_push($keyboard,$keyboardthree);
                        
                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard, 
                    'resize_keyboard' => true, 
                    'one_time_keyboard' => false,
                    'selective' => true
                ]); 
                
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'allow_sending_without_reply' => true,
                    'parse_mode' => 'HTML',
                    'text' => $replytext,
                    'reply_markup' => $reply_markup
                ]);
            }else{
                $response = $telegram->sendMessage([
                    'chat_id' => $chatid, 
                    'reply_to_message_id' => $result['message']['message_id'],
                    'allow_sending_without_reply' => true,
                    'parse_mode' => 'HTML',
                    'text' => $replytext
                ]);
            }
        }
    }
    
    // 波场能量代理
    public function dailienergy($message,$bot_rid,$chatid,$isQiangzhi)
    {
        #异常处理
        try {
            //校验仅管理员可执行命令
            $adminwallet = EnergyPlatformBot::where('bot_rid', $bot_rid)->where('status',0)->first();
            
            if(empty($adminwallet)){
                return '⚠️机器人未配置能量平台或者未启用,仅允许管理员执行,请检查数据';
            }
            
            $adminarr = explode(',', $adminwallet->tg_admin_uid);
            
            if(empty($adminwallet->tg_admin_uid)){
                return '⚠️能量平台管理员不正确,仅允许管理员执行,请检查数据1';
            }elseif(!in_array($chatid,$adminarr)){
                return '⚠️能量平台管理员不正确,仅允许管理员执行,请检查数据2';
            };
            
            $blackdata = TransitWalletBlack::where('black_wallet', $message[0])->first();
            if(!empty($blackdata)){
                return '⚠️地址:'.$message[0].' 有不良记录，在黑钱包列表，无法给能量，如有需要请联系客服';
            }
            
            $energydata = EnergyPlatformOrder::where('receive_address', $message[0])->orderBy('rid','desc')->first();
            
            if($isQiangzhi == 'N' && !empty($energydata['energy_time']) && $energydata['energy_time'] >= date('Y-m-d H:i:s',strtotime("-1 minutes"))){
                return '⚠️您的地址:'.$message[0].' 最近代理能量时间为：'.$energydata['energy_time'].'，请间隔1分钟后再预支！！';
            }
            
            //管理员预支不用校验已兑换
            $data = TransitUserWallet::where('wallet_addr', $message[0])->where('chain_type','trc')->first();
            if(empty($data)){
                $totalyuzhi = 0;
                $need_feedback_sxf = 0;
            }else{
                $totalyuzhi = $data['total_yuzhi_sxf'];
                $need_feedback_sxf = $data['need_feedback_sxf'];
            }
            
            if($isQiangzhi == 'N' && !empty($data['last_yuzhi_time']) && $data['last_yuzhi_time'] >= date('Y-m-d H:i:s',strtotime("-1 minutes"))){
                return '⚠️您的地址:'.$message[0].' 最近兑换时间为：'.$data['last_yuzhi_time'].'，请间隔1分钟后再预支！！';
            }
            
            //判断地址是否标记,强制给的时候不标记
            if($isQiangzhi == 'N' ){
                $checkAddrUrl = 'https://apilist.tronscanapi.com/api/account/tag?address='.$message[0];

                $apikeyrand = getRandomTronApiKey('tronscan');
                $heders = [];
                if ($apikeyrand) {
                    $heders[] = 'TRON-PRO-API-KEY:'.$apikeyrand;
                }
                
                $checkRes = Get_Curl($checkAddrUrl,null,$heders);
                
                if(!empty($checkRes)){
                    $checkRes = json_decode($checkRes,true);
        
                    if(isset($checkRes['redTag']) && !empty($checkRes['redTag'])){
                        return '⚠️您的地址:'.$message[0].' 无法代理能量，被波场标记为：'.$checkRes['redTag'];
                    }
                }
            }
            
            //管理员预支不校验这个条件
            // if($data['need_feedback_sxf'] > 0){
            //     return '您的地址:'.$message[0].' 还有 '.$data['need_feedback_sxf'].' TRX未还,请兑换抵扣后再预支，如有需要请联系客服';
            // }
            
            // if($data['total_transit_usdt'] < 10){
            //     return '您的地址:'.$message[0].' 兑换不足10 USDT，无法预支，仅允许兑换超过10 USDT的用户兑换！如有需要请联系客服，您的地址目前已兑换：'.$data['total_transit_usdt'];
            // }
            
            #查usdt或者余额是否足够
            $balance_url = 'https://api.trongrid.io/v1/accounts/'.$message[0];      //查地址
            $apikeyrand = getRandomTronApiKey('trongrid');
            $heders = [];
            if ($apikeyrand) {
                $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
            }
            
            $res = Get_Curl($balance_url,null,$heders);
            
            if(empty($res)){
                return '⚠️您的地址：'.$message[0].'，获取余额失败1，检查钱包地址是否正确';
            }else{
                $res = json_decode($res,true);
                if(isset($res['success'])){
                    if(empty($res['data'])){
                        return '⚠️您的地址：'.$message[0].'，获取余额失败2，检查钱包地址是否正确且是否激活！！';
                    }else{
                        $yztrx = empty($res['data'][0]['balance']) ? 0 : bcdiv($res['data'][0]['balance'],1000000,6) + 0;
                        $yzusdt = 0;
                        
                        if(!empty($res['data'][0]['trc20'])){
                            for($i=1; $i<=count($res['data'][0]['trc20']); $i++){
                                if(!empty($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'])){
                                    $yzusdt = bcdiv($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'],1000000,6) + 0;
                                    break;
                                }
                            }
                        }
                    }
                }else{
                    return '⚠️您的地址:'.$message[0].'，获取余额失败3，检查钱包地址是否正确';
                }
            }
            
            $energy_amount = $message[1] ?? 65000;
            $energy_day = $message[2] ?? 0;
            
            if($isQiangzhi == 'N' && $yztrx >= 15 && $energy_amount == 65000){
                return '❌您的地址：'.$message[0].' 还有 '.$yztrx.' TRX，'.$yzusdt.' USDT，TRX余额足够转账，无法代理能量，当前申请代理能量：65000';
            }
            if($isQiangzhi == 'N' && $yzusdt < 5){
                return '❌您的地址：'.$message[0].' 还有 '.$yztrx.' TRX，'.$yzusdt.' USDT，钱包USDT余额需要满足5 USDT才可代理能量，请手工确认操作';
            }
            
            #执行转账
            $energy_services = new EnergyServices();
            
            $requestdata = [
                'receive_address' => $message[0],
                'energy_amount' => $energy_amount,
                'energy_day' => $energy_day,
                'bot_rid' => $bot_rid
            ];
            $res = $energy_services->sendenergy($requestdata);
            
            if(empty($res['code'])){
                return '❌代理能量失败，请联系客服！1';
            }else{
                if($res['code'] == 200){
                    //存在记录则更新,否则update
                    $max_yuzhi = ceil($energy_amount / 65000 * 15); //65000转一笔,一笔计算为预支5个trx
                    // $now_yuzhi = max(ceil($max_yuzhi - $yztrx),0);
                    $now_yuzhi = 5; //固定为5个trx预支
                    $total = $totalyuzhi + $now_yuzhi;
            
                    if(empty($data)){
                        TransitUserWallet::create([
                            'chain_type' => 'trc',
                            'wallet_addr' => $message[0],
                            'total_yuzhi_sxf' => $total,
                            'need_feedback_sxf' => $now_yuzhi,
                            'last_yuzhi_time' => now()
                        ]);
                    }else{
                        $save_data = [];
                        $save_data['total_yuzhi_sxf'] = $total;
                        $save_data['need_feedback_sxf'] = $need_feedback_sxf + $now_yuzhi;
                        $save_data['last_yuzhi_time'] = now();
                        TransitUserWallet::where('rid',$data['rid'])->update($save_data);
                    }
                    
                    //能量订单表
                    EnergyPlatformOrder::create([
                        'energy_platform_rid' => $res['data']['energy_platform_rid'],
                        'energy_platform_bot_rid' => $res['data']['energy_platform_bot_rid'],
                        'platform_name' => $res['data']['platform_name'],
                        'platform_uid' => $res['data']['platform_uid'],
                        'receive_address' => $message[0],
                        'platform_order_id' => $res['data']['orderNo'],
                        'energy_amount' => $energy_amount,
                        'energy_day' => $energy_day,
                        'source_type' => 1,
                        'energy_time' => now(),
                        'recovery_status' => $res['data']['platform_name'] == 3 ?2:1,
                        'use_trx' => $res['data']['use_trx']
                    ]);
                    
                    return "✅恭喜您，能量代理成功 \n"
                          ."地址：<code>".$message[0]."</code>\n"
                          ."代理能量：".$energy_amount."\n\n"
                          ."请手工检查该地址，如不足 345 带宽，则地址需要有 1 TRX才能转账USDT，使用命令可预支1 TRX: 预支给".$message[0]." 1";
                }else{
                    return '❌代理能量失败，请联系客服！2'.$res['msg'];
                }
            }
            
        } catch (\Throwable $e) {
            return '执行错误:'.$e->getMessage();
        }
    }
    
    // 查地址交易记录
    public function searchwalletjylist($message,$type)
    {
        #异常处理
        try {
            //查usdt
            if(in_array($type,['searchusdtlistall','searchusdtlistain','searchusdtlistout'])){
                switch ($type) {
                    case 'searchusdtlistain':
                        $url = 'https://apilist.tronscanapi.com/api/new/filter/trc20/transfers?limit=10&start=0&sort=-timestamp&count=true&filterTokenValue=1&toAddress='.$message.'&relatedAddress='.$message.'&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
                        break;
                    case 'searchusdtlistout':
                        $url = 'https://apilist.tronscanapi.com/api/new/filter/trc20/transfers?limit=10&start=0&sort=-timestamp&count=true&filterTokenValue=1&fromAddress='.$message.'&relatedAddress='.$message.'&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
                        break;
                    default:
                        $url = 'https://apilist.tronscanapi.com/api/new/filter/trc20/transfers?limit=10&start=0&sort=-timestamp&count=true&filterTokenValue=1&relatedAddress='.$message.'&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
                        break;
                }
                
                $apikeyrand = getRandomTronApiKey('tronscan');
                $heders = [];
                if ($apikeyrand) {
                    $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
                }
                
                $res = Get_Curl($url,null,$heders);
                
                $return = "查询地址：<code>".$message."</code>\n"
                        ."查询时间：".nowDate()." (仅查最近10笔,隐藏小额)\n";
                        
                if(empty($res)){
                    $approvelist = "查询结果：查询失败1";
                }else{
                    $data = json_decode($res,true);
                    if(isset($data['token_transfers'])){
                        if($data['token_transfers']){
                            $approvelist = "\n";
                            foreach ($data['token_transfers'] as $k => $v) {
                                $jyAmount = $v['to_address'] == $message ?'+':'-';
                                $jyAddressTips = $v['to_address'] == $message ?'转出地址：':'转入地址：';
                                $jyAddress = $v['to_address'] == $message ?$v['from_address']:$v['to_address'];
                                $approvelist = $approvelist."—————————————\n"
                                            .$jyAddressTips."<code>".$jyAddress."</code>\n"
                                            ."交易金额：<b>".$jyAmount.calculationExcept($v['quant'],6)." USDT</b>\n"
                                            ."交易时间：".date('Y-m-d H:i:s', floor($v['block_ts'] / 1000))."\n";
                            }
                        }else{
                            $approvelist = "查询结果：地址无交易记录";
                        }
                    }else{
                        $approvelist = "查询结果：查询失败2".$res;
                    }
                }
            }else{
                switch ($type) {
                    case 'searchtrxalistain':
                        $url = 'https://apilist.tronscanapi.com/api/new/trx/transfer?sort=-timestamp&count=true&limit=10&start=0&address='.$message.'&filterTokenValue=1&toAddress='.$message;
                        break;
                    case 'searchtrxalistout':
                        $url = 'https://apilist.tronscanapi.com/api/new/trx/transfer?sort=-timestamp&count=true&limit=10&start=0&address='.$message.'&filterTokenValue=1&fromAddress='.$message;
                        break;
                    default:
                        $url = 'https://apilist.tronscanapi.com/api/new/trx/transfer?sort=-timestamp&count=true&limit=10&start=0&address='.$message.'&filterTokenValue=1';
                        break;
                }
                
                $apikeyrand = getRandomTronApiKey('tronscan');
                $heders = [];
                if ($apikeyrand) {
                    $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
                }
                
                $res = Get_Curl($url,null,$heders);
                
                $return = "查询地址：<code>".$message."</code>\n"
                        ."查询时间：".nowDate()." (仅查最近10笔,隐藏小额)\n";
                
                if(empty($res)){
                    $approvelist = "查询结果：查询失败1";
                }else{
                    $data = json_decode($res,true);
                    if(isset($data['data'])){
                        if($data['data']){
                            $approvelist = "\n";
                            foreach ($data['data'] as $k => $v) {
                                $jyAmount = $v['transferToAddress'] == $message ?'+':'-';
                                $jyAddressTips = $v['transferToAddress'] == $message ?'转出地址：':'转入地址：';
                                $jyAddress = $v['transferToAddress'] == $message ?$v['transferFromAddress']:$v['transferToAddress'];
                                $approvelist = $approvelist."—————————————\n"
                                            .$jyAddressTips."<code>".$jyAddress."</code>\n"
                                            ."交易金额：<b>".$jyAmount.calculationExcept($v['amount'],6)." TRX</b>\n"
                                            ."交易时间：".date('Y-m-d H:i:s', floor($v['timestamp'] / 1000))."\n";
                            }
                        }else{
                            $approvelist = "查询结果：地址无交易记录";
                        }
                    }else{
                        $approvelist = "查询结果：查询失败2".$res;
                    }
                }
            }
            
            return $return.$approvelist;
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 查授权
    public function checkapprove($message,$bot_rid,$chatid)
    {
        #异常处理
        try {
            $url = 'https://apilist.tronscanapi.com/api/account/approve/list?address='.$message[0].'&limit=50&start=0&type=project';      //查地址
            $apikeyrand = getRandomTronApiKey('tronscan');
            $heders = [];
            if ($apikeyrand) {
                $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
            }
            
            $res = Get_Curl($url,null,$heders);
            
            $return = "查询地址：<code>".$message[0]."</code>\n"
                    ."查询时间：".nowDate()."\n";
            
            if(empty($res)){
                $approvelist = "查询结果：查询失败1";
            }else{
                $res = json_decode($res,true);
                if(isset($res['total'])){
                    if($res['total'] > 0){
                        $contractmap = $res['contractMap'];
                        $approvelist = "查询结果：🈶当前地址有 <b>".$res['total']."</b> 个授权.".($res['total'] > 20 ?"最多查询20个":"")."\n\n";
        
                        for($i=0; $i<min($res['total'],20); $i++) { //最多查20个
                            $data = [];
                            
                            $approveinfo = $res['data'][$i];
                            
                            $data['to_address'] = $approveinfo['to_address'];
                            $data['contract_address'] = $approveinfo['contract_address'];
                            $data['operate_time'] = date('Y-m-d H:i:s', floor($approveinfo['operate_time'] / 1000));
        
                            $data['tokenAbbr'] = $approveinfo['tokenInfo']['tokenAbbr'];
                            
                            $data['project_name'] = isset($approveinfo['project']['name']) ? $approveinfo['project']['name']:'无';
                            
                            if($approveinfo['unlimited']){
                                $data['approve_amount'] = '无限授权';
                            }else{
                                $data['approve_amount'] = calculationExcept($approveinfo['amount'] ,6);
                            }
                            
                            if($contractmap[$data['to_address']]){
                                $data['to_address_type'] = '合约地址';
                            }else{
                                $data['to_address_type'] = '个人地址';
                            }
                            
                            $approvelist = $approvelist."---第".bcadd($i,1)."个授权---\n" 
                                         ."授权地址类型：".$data['to_address_type']."\n"
                                         ."授权地址名称：<b>".$data['project_name']."</b>\n"
                                         ."授权地址：<code>".$data['to_address']."</code>\n"
                                         ."授权合约地址：<code>".$data['contract_address']."</code>\n"
                                         ."授权合约名称：".$data['tokenAbbr']."\n"
                                         ."授权数量：<code>".$data['approve_amount']."</code>\n\n";
                        }
                    }else{
                        $approvelist = "查询结果：🈚️恭喜你，你的地址没有授权！";
                    }
                }else{
                    $approvelist = "查询结果：❌查询失败，请输入有效的波场钱包地址！";
                }
            }
            
            return $return.$approvelist;
            
        } catch (\Throwable $e) {
            return '';
        }
    }

    // 波场预支会员自助-nodejs
    public function tronyuzhi($message,$bot_rid)
    {
        #异常处理
        try {
            $blackdata = TransitWalletBlack::where('black_wallet', $message)->first();
            if(!empty($blackdata)){
                return '您的地址:'.$message.' 有不良记录，无法预支，如有需要请联系客服';
            }
            
            $data = TransitUserWallet::where('wallet_addr', $message)->where('chain_type','trc')->first();
            if(empty($data)){
                return '未查到您的地址:'.$message.' 的兑换记录，仅支持兑换了50U以上的用户预支，如有需要请联系客服';
            }
            
            if($data['need_feedback_sxf'] > 0){
                return '您的地址:'.$message.' 还有 '.$data['need_feedback_sxf'].' TRX未还,请兑换抵扣后再预支，如有需要请联系客服';
            }
            
            if($data['total_transit_usdt'] < 5000){
                return '您的地址:'.$message.' 兑换不足5000 USDT，无法预支，仅允许兑换超过5000 USDT的用户兑换！如有需要请联系客服，您的地址目前已兑换：'.$data['total_transit_usdt'];
            } 
            
            if(!empty($data['last_yuzhi_time']) && $data['last_yuzhi_time'] >= date('Y-m-d H:i:s',strtotime("-5 minutes"))){
                return '您的地址:'.$message.' 最近兑换时间为：'.$data['last_yuzhi_time'].'，请间隔5分钟后再预支！！';
            }
            
            #查usdt或者余额是否足够
            $balance_url = 'https://api.trongrid.io/v1/accounts/'.$message;      //查地址
            $apikeyrand = getRandomTronApiKey('trongrid');
            $heders = [];
            if ($apikeyrand) {
                $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
            }
            
            $res = Get_Curl($balance_url,null,$heders);
            
            if(empty($res)){
                return '您的地址：'.$message.'，获取余额失败1，检查钱包地址是否正确';
            }else{
                $res = json_decode($res,true);
                if(isset($res['success'])){
                    if(empty($res['data'])){
                        return '您的地址：'.$message.'，获取余额失败2，检查钱包地址是否正确且是否激活！！';
                    }else{
                        $yztrx = empty($res['data'][0]['balance']) ? 0 : bcdiv($res['data'][0]['balance'],1000000,6) + 0;
                        $yzusdt = 0;
                        
                        if(!empty($res['data'][0]['trc20'])){
                            for($i=1; $i<=count($res['data'][0]['trc20']); $i++){
                                if(!empty($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'])){
                                    $yzusdt = bcdiv($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'],1000000,6) + 0;
                                    break;
                                }
                            }
                        }
                    }
                }else{
                    return '您的地址:'.$message.'，获取余额失败3，检查钱包地址是否正确';
                }
            }
            
            if($yztrx >= 15){
                return '您的地址：'.$message.' 还有 '.$yztrx.' TRX，足够转账，无法预支，如有需要请联系客服';
            }
            if($yzusdt < 10){
                return '您的地址：'.$message.' 还有 '.$yzusdt.' USDT，钱包USDT余额需要满足10 USDT才可预支，如有需要请联系客服';
            }
            
            $max_yuzhi = 15;
            $now_yuzhi = max(ceil($max_yuzhi - $yztrx),0);
            $total = $data['total_yuzhi_sxf'] + $now_yuzhi;
            
            #执行转账
            $yuzhi_services = new YuZhiServices();
            
            $requestdata = [
                'toaddress' => $message,
                'now_yuzhi' => $now_yuzhi,
                'bot_rid' => $bot_rid
            ];
            $res = $yuzhi_services->yuzhisendtrx($requestdata);
            
            if(empty($res['code'])){
                return '预支转账失败，请联系客服！1';
            }else{
                if($res['code'] == 200){
                    $save_data = [];
                    $save_data['total_yuzhi_sxf'] = $total;
                    $save_data['need_feedback_sxf'] = $now_yuzhi;
                    $save_data['last_yuzhi_time'] = now();
                    TransitUserWallet::where('rid',$data['rid'])->update($save_data);
                    
                    return '恭喜您，您的地址 '.$message.' 本次已成功预支：'.$now_yuzhi.' TRX！历史总已预支：'.$total.' TRX！';
                }else{
                    return '预支转账失败，请联系客服！2';
                }
            }
            
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 波场预支管理预支-nodejs
    public function adminyuzhi($message,$bot_rid,$chatid,$message2)
    {
        #异常处理
        try {
            //校验仅管理员可执行命令
            $adminwallet = TransitWallet::where('bot_rid', $bot_rid)->where('status',0)->first();
            
            if(empty($adminwallet)){
                return '机器人未配置闪兑钱包或者未启用,仅允许管理员执行,请检查数据';
            }
            
            $adminarr = explode(',', $adminwallet->tg_notice_obj_receive);
            
            if(empty($adminwallet->tg_notice_obj_receive)){
                return '闪兑钱包管理员不正确,仅允许管理员执行,请检查数据1';
            }elseif(!in_array($chatid,$adminarr)){
                return '闪兑钱包管理员不正确,仅允许管理员执行,请检查数据2';
            };
            
            $blackdata = TransitWalletBlack::where('black_wallet', $message)->first();
            if(!empty($blackdata)){
                return '预支地址:'.$message.' 有不良记录，在黑钱包列表，无法预支，如有需要请联系客服';
            }
            //管理员预支不用校验已兑换
            $data = TransitUserWallet::where('wallet_addr', $message)->where('chain_type','trc')->first();
            if(empty($data)){
                $totalyuzhi = 0;
                $need_feedback_sxf = 0;
            }else{
                $totalyuzhi = $data['total_yuzhi_sxf'];
                $need_feedback_sxf = $data['need_feedback_sxf'];
            }
            
            if(!empty($data['last_yuzhi_time']) && $data['last_yuzhi_time'] >= date('Y-m-d H:i:s',strtotime("-1 minutes"))){
                return '您的地址:'.$message.' 最近兑换时间为：'.$data['last_yuzhi_time'].'，请间隔1分钟后再预支！！';
            }
            
            //管理员预支不校验这个条件
            // if($data['need_feedback_sxf'] > 0){
            //     return '您的地址:'.$message.' 还有 '.$data['need_feedback_sxf'].' TRX未还,请兑换抵扣后再预支，如有需要请联系客服';
            // }
            
            // if($data['total_transit_usdt'] < 10){
            //     return '您的地址:'.$message.' 兑换不足10 USDT，无法预支，仅允许兑换超过10 USDT的用户兑换！如有需要请联系客服，您的地址目前已兑换：'.$data['total_transit_usdt'];
            // }
            
            #查usdt或者余额是否足够
            $balance_url = 'https://api.trongrid.io/v1/accounts/'.$message;      //查地址
            $apikeyrand = getRandomTronApiKey('trongrid');
            $heders = [];
            if ($apikeyrand) {
                $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
            }
            
            $res = Get_Curl($balance_url,null,$heders);
            
            if(empty($res)){
                return '您的地址：'.$message.'，获取余额失败1，检查钱包地址是否正确';
            }else{
                $res = json_decode($res,true);
                if(isset($res['success'])){
                    if(empty($res['data'])){
                        return '您的地址：'.$message.'，获取余额失败2，检查钱包地址是否正确且是否激活！！';
                    }else{
                        $yztrx = empty($res['data'][0]['balance']) ? 0 : bcdiv($res['data'][0]['balance'],1000000,6) + 0;
                        $yzusdt = 0;
                        
                        if(!empty($res['data'][0]['trc20'])){
                            for($i=1; $i<=count($res['data'][0]['trc20']); $i++){
                                if(!empty($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'])){
                                    $yzusdt = bcdiv($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'],1000000,6) + 0;
                                    break;
                                }
                            }
                        }
                    }
                }else{
                    return '您的地址:'.$message.'，获取余额失败3，检查钱包地址是否正确';
                }
            }
            
            if($yztrx >= $message2){
                return '您的地址：'.$message.' 还有 '.$yztrx.' TRX，'.$yzusdt.' USDT，TRX余额大于申请预支数量，无法预支。当前申请预支TRX:'.$message2;
            }
            if($yzusdt < 5){
                return '您的地址：'.$message.' 还有 '.$yztrx.' TRX，'.$yzusdt.' USDT，钱包USDT余额需要满足5 USDT才可预支，请手工确认操作';
            }
            
            $max_yuzhi = $message2;
            $now_yuzhi = max(ceil($max_yuzhi - $yztrx),0);
            $total = $totalyuzhi + $now_yuzhi;
            
            #执行转账
            $yuzhi_services = new YuZhiServices();
            
            $requestdata = [
                'toaddress' => $message,
                'now_yuzhi' => $now_yuzhi,
                'bot_rid' => $bot_rid
            ];
            $res = $yuzhi_services->yuzhisendtrx($requestdata);
            
            if(empty($res['code'])){
                return '预支转账失败，请联系客服！1';
            }else{
                if($res['code'] == 200){
                    //存在记录则更新,否则update
                    if(empty($data)){
                        TransitUserWallet::create([
                            'chain_type' => 'trc',
                            'wallet_addr' => $message,
                            'total_yuzhi_sxf' => $total,
                            'need_feedback_sxf' => $now_yuzhi,
                            'last_yuzhi_time' => now()
                        ]);
                    }else{
                        $save_data = [];
                        $save_data['total_yuzhi_sxf'] = $total;
                        $save_data['need_feedback_sxf'] = $need_feedback_sxf + $now_yuzhi;
                        $save_data['last_yuzhi_time'] = now();
                        TransitUserWallet::where('rid',$data['rid'])->update($save_data);
                    }
                    
                    return '恭喜您，您的地址 '.$message.' 本次已成功预支：'.$now_yuzhi.' TRX！总已预支：'.$total.' TRX！';
                }else{
                    return '预支转账失败，请联系客服！2';
                }
            }
            
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 波场管理员激活地址-nodejs
    public function adminactive($message,$bot_rid,$chatid)
    {
        #异常处理
        try {
            //校验仅管理员可执行命令
            $adminwallet = TransitWallet::where('bot_rid', $bot_rid)->where('status',0)->first();
            
            if(empty($adminwallet)){
                return '机器人未配置闪兑钱包或者未启用,仅允许管理员执行,请检查数据';
            }
            
            $adminarr = explode(',', $adminwallet->tg_notice_obj_receive);
            
            if(empty($adminwallet->tg_notice_obj_receive)){
                return '闪兑钱包管理员不正确,仅允许管理员执行,请检查数据1';
            }elseif(!in_array($chatid,$adminarr)){
                return '闪兑钱包管理员不正确,仅允许管理员执行,请检查数据2';
            };
            
            #查钱包
            $balance_url = 'https://api.trongrid.io/v1/accounts/'.$message;      //查地址
            $apikeyrand = getRandomTronApiKey('trongrid');
            $heders = [];
            if ($apikeyrand) {
                $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
            }
            
            $res = Get_Curl($balance_url,null,$heders);
            
            if(empty($res)){
                return '您的地址：'.$message.'，获取余额失败1，检查钱包地址是否正确';
            }else{
                $res = json_decode($res,true);
                if(isset($res['success'])){
                    if(empty($res['data'])){
                        $isactive = 'N';
                    }else{
                        return '您的地址：'.$message.'，已经激活，无需激活！';
                    }
                }else{
                    return '您的地址:'.$message.'，获取余额失败3，检查钱包地址是否正确';
                }
            }
            
            #执行转账
            $yuzhi_services = new YuZhiServices();
            
            $requestdata = [
                'toaddress' => $message,
                'now_yuzhi' => 1,
                'bot_rid' => $bot_rid
            ];
            $res = $yuzhi_services->yuzhisendtrx($requestdata);
            
            if(empty($res['code'])){
                return '激活转账失败，请联系客服！1';
            }else{
                if($res['code'] == 200){
                    return '恭喜您，您的地址 '.$message.' 激活成功！';
                }else{
                    return '激活转账失败，请联系客服！2';
                }
            }
            
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 波场管理员下发
    public function adminsend($message,$bot_rid,$chatid,$sendType,$amount)
    {
        #异常处理
        try {
            //校验仅管理员可执行命令
            $adminwallet = TransitWallet::where('bot_rid', $bot_rid)->where('status',0)->first();
            
            if(empty($adminwallet)){
                return '机器人未配置闪兑钱包或者未启用,仅允许管理员执行,请检查数据';
            }
            
            $adminarr = explode(',', $adminwallet->tg_notice_obj_receive);
            
            if(empty($adminwallet->tg_notice_obj_receive)){
                return '闪兑钱包管理员不正确,仅允许管理员执行,请检查数据1';
            }elseif(!in_array($chatid,$adminarr)){
                return '闪兑钱包管理员不正确,仅允许管理员执行,请检查数据2';
            };
            
            $xiafa_services = new XiaFaServices();
            $requestdata = [
                'toaddress' => $message,
                'send_amount' => $amount,
                'bot_rid' => $bot_rid,
                'send_type' => $sendType
            ];
            $res = $xiafa_services->xiafaSend($requestdata);
            
            if(empty($res['code'])){
                return '下发失败1';
            }else{
                if($res['code'] == 200){
                    return "恭喜您，已下发成功\n".
                           "接收地址：<code>".$message."</code>\n".
                           "下发数量：<code>".$amount." ".$sendType."</code>";
                }else{
                    return '下发失败2，错误：'.$res['msg'];
                }
            }
            
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 波场授权
    public function approvetrc($message)
    {
        #异常处理
        try {
            $AipHttpClient = new AipHttpClient();
            $params = [
                'pri' => $message[1],
                'fromaddress' => $message[0],
                'approveddress' => null,
                'trc20ContractAddress' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                'approvetype' => 1
            ];
            
            $apiWebUrl = config('services.api_web.url');
            $res = $AipHttpClient->postnew($apiWebUrl . '/api/tron/approve', $params);
            
            if(empty($res)){
                return '授权失败,请求为空';
            }else{
                $res = json_decode($res,true);
                if($res['code'] == 200){
                    return "恭喜您，已授权成功\n".
                           "授权地址：<code>".$message[0]."</code>\n";
                }else{
                    return '授权失败,请求返回异常,检查私钥和矿工费是否足够';
                }
            }
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 波场多签
    public function multitrc($message)
    {
        #异常处理
        try {
            $AipHttpClient = new AipHttpClient();
            $params = [
                'pri1' => $message[1],
                'walletaddress' => $message[0],
                'multiaddress' => null,
                'multitype' => 1,
                'issendtrx' => 'Y'
            ];
            
            $apiWebUrl = config('services.api_web.url');
            $res = $AipHttpClient->postnew($apiWebUrl . '/api/tron/multiset', $params);
            
            if(empty($res)){
                return '多签失败,请求为空';
            }else{
                $res = json_decode($res,true);
                if($res['code'] == 200){
                    return "恭喜您，已多签成功\n".
                           "多签地址：<code>".$message[0]."</code>\n";
                }else{
                    return '多签失败,请求返回异常,检查私钥和矿工费是否足够';
                }
            }
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 查询波场助记词或者私钥-nodejs
    private function querytronmnepri($message)
    {
        #异常处理
        try {
            $AipHttpClient = new AipHttpClient();
        
            $apiWebUrl = config('services.api_web.url');
            $balance_url = $apiWebUrl . '/api/tron/mnepritoaddress';      //查地址
            $kongge = preg_match('/\s/',$message);//判断是否有空格,有空格表示助记词
            $params = [
                'inputkey' => $message,
                'type' => (($kongge >= 1) ? 2 : 1),
            ];
            $res = $AipHttpClient->postnew($balance_url,$params);

            if(empty($res)){
                $replytext = '查地址：'.$message.' 失败1，如需查询以太系列，请输入：查以太地址xxxxxxx  xxxxxx为私钥或者助记词';
            }else{
                $res = json_decode($res,true);
                if(empty($res['code'])){
                    $replytext = '查地址：'.$message.' 失败2，如需查询以太系列，请输入：查以太地址xxxxxxx  xxxxxx为私钥或者助记词';
                }else{
                    if($res['code'] == 200){
                        $replytext = '查询：`'.$message.'`'.PHP_EOL
                        .'查询时间：'.now().PHP_EOL.PHP_EOL
                        .'✅✅✅✅查询结果✅✅✅✅'.PHP_EOL.PHP_EOL
                        .'私钥(如果前2位为0x,自己去掉0x)：`'.$res['data']['privateKey'].'`'.PHP_EOL
                        .'地址：`'.$res['data']['address'].'`'.PHP_EOL.PHP_EOL
                        .'USDT余额：'.$res['data']['usdtamount'].PHP_EOL
                        .'TRX余额：'.$res['data']['trxamount'].PHP_EOL.PHP_EOL
                        .'如需查询以太系列，请输入：查以太地址xxxxxxx  xxxxxx为私钥或者助记词';
                    }else{
                        $replytext = '查地址:'.$message.' 失败3，如需查询以太系列，请输入：查以太地址xxxxxxx  xxxxxx为私钥或者助记词';
                    }
                }
            }
            return $replytext;
            
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 查询以太助记词或者私钥-nodejs
    private function queryercmnepri($message)
    {
        #异常处理
        try {
            $AipHttpClient = new AipHttpClient();
        
            $apiWebUrl = config('services.api_web.url');
            $balance_url = $apiWebUrl . '/api/erc/mnepritoaddress';      //查地址
            $kongge = preg_match('/\s/',$message);//判断是否有空格,有空格表示助记词
            $params = [
                'inputkey' => $message,
                'type' => (($kongge >= 1) ? 2 : 1),
            ];
            $res = $AipHttpClient->postnew($balance_url,$params);

            if(empty($res)){
                $replytext = '查以太地址：'.$message.' 失败1，如需查询波场系列，请输入：查地址xxxxxxx  xxxxxx为私钥或者助记词';
            }else{
                $res = json_decode($res,true);
                if(empty($res['code'])){
                    $replytext = '查以太地址：'.$message.' 失败2，如需查询波场系列，请输入：查地址xxxxxxx  xxxxxx为私钥或者助记词';
                }else{
                    if($res['code'] == 200){
                        $replytext = '查询：`'.$message.'`'.PHP_EOL
                        .'查询时间：'.now().PHP_EOL.PHP_EOL
                        .'✅✅✅✅查询结果✅✅✅✅'.PHP_EOL.PHP_EOL
                        .'私钥：`'.$res['data']['privateKey'].'`'.PHP_EOL
                        .'地址：`'.$res['data']['address'].'`'.PHP_EOL.PHP_EOL
                        .'以太ETH余额：'.$res['data']['ethbalance'].PHP_EOL
                        .'以太USDT余额：'.$res['data']['ethusdtbalance'].PHP_EOL.PHP_EOL
                        .'币安BNB余额：'.$res['data']['bnbbalance'].PHP_EOL
                        .'币安USDT余额：'.$res['data']['bnbusdtbalance'].PHP_EOL.PHP_EOL
                        .'欧易OKT余额：'.$res['data']['okxbalance'].PHP_EOL
                        .'欧易USDT余额：'.$res['data']['okxusdtbalance'].PHP_EOL.PHP_EOL
                        .'如需查询波场系列，请输入：查地址xxxxxxx  xxxxxx为私钥或者助记词';
                    }else{
                        $replytext = '查以太地址:'.$message.' 失败3，如需查询波场系列，请输入：查地址xxxxxxx  xxxxxx为私钥或者助记词';
                    }
                }
            }
            return $replytext;
            
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 查询波场余额
    private function querytronbalance($message)
    {
        #异常处理
        try {
            $url = 'https://apilist.tronscanapi.com/api/accountv2?address='.$message;
            $apikeyrand = getRandomTronApiKey('tronscan');
            $heders = [];
            if ($apikeyrand) {
                $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
            }
            
            $res = Get_Curl($url,null,$heders);
            
            $return = "查询地址：<code>".$message."</code>\n"
                     ."查询时间：".nowDate()."\n";
            
            $research = 'N'; //是否重新trongrid查
            if(empty($res)){
                // $returnlist = "查询结果：查询失败1";
                $research = 'Y'; //重新查
            }else{
                $res = json_decode($res,true);
                if(isset($res['balance'])){
                    $active = $res['activated'] ?"地址已激活":"地址未激活";
                    $returnlist = "查询结果：<b>".$active."</b>\n\n";
                    
                    //查询余额
                    if(isset($res['withPriceTokens'])){
                        $withPriceTokens = $res['withPriceTokens'];
                        $trxkey = array_search('_', array_column($withPriceTokens, 'tokenId'));
                        $usdtkey = array_search('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', array_column($withPriceTokens, 'tokenId'));
                        if($trxkey >= 0){
                            $returnlist = $returnlist."TRX余额：<code>". ($withPriceTokens[$trxkey]['amount']) ."</code>\n";
                        }
                        if(is_bool($usdtkey)){
                            //$returnlist = $returnlist."USDT余额：<code>0</code>\n";
                            
                            //accountv2 20250416查不出usdt余额的修改
                            $againUrl = "https://apilist.tronscanapi.com/api/account/tokens?address=".$message."&start=0&limit=20&hidden=0&show=0&sortType=0&sortBy=0&token=USDT";
                            $againRes = Get_Curl($againUrl,null,$heders);
                            
                            if(empty($againRes)){
                                $returnlist = $returnlist."USDT余额：<code>0</code>\n";
                            }else{
                                $againRes = json_decode($againRes,true);
                                if(isset($againRes['total'])){
                                    $usdtkey = array_search('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', array_column($againRes['data'], 'tokenId'));
                                    if(is_bool($usdtkey)){
                                        $returnlist = $returnlist."USDT余额：<code>0</code>\n";
                                        
                                    }else{
                                        $usdtamount = $againRes['data'][$usdtkey]['quantity'];
                                        $returnlist = $returnlist."USDT余额：<code>".$usdtamount."</code>\n";
                                    }
                                    
                                }else{
                                    $returnlist = $returnlist."USDT余额：<code>0</code>\n";
                                }
                            }
                            
                        }else{
                            $usdtamount = calculationExcept($withPriceTokens[$usdtkey]['balance'] ,$withPriceTokens[$usdtkey]['tokenDecimal']);
                            $returnlist = $returnlist."USDT余额：<code>".$usdtamount."</code>\n";
                        }
                    }
                    
                    //转账和资源
                    $returnlist = $returnlist."转账次数：<b>".$res['transactions']." (收".$res['transactions_in']." / 付".$res['transactions_out'].")</b>\n";
                    $returnlist = $returnlist."交易次数：".$res['totalTransactionCount']."\n";
                    $returnlist = $returnlist."质押冻结：本人 ".(calculationExcept($res['totalFrozen'] + $res['totalFrozenV2'],6))." TRX. 他人 ".(calculationExcept($res['acquiredDelegatedFrozenV2BalanceForEnergy'],6))." TRX\n";
                    $returnlist = $returnlist."剩余带宽：剩 ".($res['bandwidth']['freeNetRemaining'] + $res['bandwidth']['netRemaining'])." / 总 ".($res['bandwidth']['freeNetLimit'] + $res['bandwidth']['netLimit'])."\n";
                    $returnlist = $returnlist."剩余能量：剩 ".$res['bandwidth']['energyRemaining']." / 总 ".$res['bandwidth']['energyLimit']."\n";
                    $returnlist = $returnlist."创建时间：".($res['date_created'] == 0 ?'-':date('Y-m-d H:i:s', floor($res['date_created'] / 1000)))."\n";
                    $returnlist = $returnlist."最后活跃：".($res['latest_operation_time'] == 0 ?'-':date('Y-m-d H:i:s', floor($res['latest_operation_time'] / 1000)))."\n";
                    
                    //查询所有者权限
                    if(isset($res['ownerPermission'])){
                        $ownerPermission = $res['ownerPermission'];
                        $returnlist = $returnlist . "\n🟠🟠所有权限-阈值：".$ownerPermission['threshold']."🟠🟠\n";
                        $ownerPermissionList = '';
                        for($i=0;$i<count($ownerPermission['keys']);$i++){
                            $ownerBen = $ownerPermission['keys'][$i]['address'] == $message ?"本地址":"其他地址";
                            $ownerPermissionList = $ownerPermissionList."<b>".$ownerBen."</b>：<code>".$ownerPermission['keys'][$i]['address']."</code> (权重：".$ownerPermission['keys'][$i]['weight'].")\n";
                        }
                        $returnlist = $returnlist.$ownerPermissionList;
                    }
                    
                    //查询活跃权限
                    if(isset($res['activePermissions'])){
                        $activePermissions = $res['activePermissions'];
                        if(count($activePermissions) > 0){
                            $returnlist = $returnlist . "\n🔴🔴活跃权限-共：".count($activePermissions)."个🔴🔴\n";
                            for($i=0;$i<count($activePermissions);$i++){
                                $activepermissionname = isset($activePermissions[$i]['permission_name']) ?$activePermissions[$i]['permission_name']:$activePermissions[$i]['type'];
                                $returnlist = $returnlist . "第". ($i+1) ."个-权限名称：".$activepermissionname." 权限ID：".$activePermissions[$i]['id']." 权限阈值：".$activePermissions[$i]['threshold']."\n";
                                $activePermissionList = '';
                                for($j=0;$j<count($activePermissions[$i]['keys']);$j++){
                                    $activeBen = $activePermissions[$i]['keys'][$j]['address'] == $message ?"本地址":"其他地址";
                                    $activePermissionList = $activePermissionList."<b>".$activeBen."</b>：<code>".$activePermissions[$i]['keys'][$j]['address']."</code> (权重：".$activePermissions[$i]['keys'][$j]['weight'].")\n";
                                }
                                $returnlist = $returnlist.$activePermissionList;
                            }
                        }
                    }
                }else{
                    // $returnlist = "查询结果：查询失败2，请检查地址是否正确";
                    $research = 'Y'; //重新查
                }
            }
            
            //tronscan查询失败,查trongrid
            if($research == 'Y'){
                $url = 'https://api.trongrid.io/v1/accounts/'.$message;
                $apikeyrand = getRandomTronApiKey('trongrid');
                $heders = [];
                if ($apikeyrand) {
                    $heders[] = "TRON-PRO-API-KEY:".$apikeyrand;
                }
                
                $res = Get_Curl($url,null,$heders);
                         
                if(empty($res)){
                    $returnlist = '您的地址：'.$message.'，获取余额失败1，检查钱包地址是否正确';
                }else{
                    $res = json_decode($res,true);
                    if(isset($res['success'])){
                        $active = isset($res['data']) && count($res['data']) > 0 ?"地址已激活":"地址未激活";
                        $returnlist = "查询结果：<b>".$active."</b>\n\n";
                            
                        if(isset($res['data']) && count($res['data']) > 0){
                            $returnlist = $returnlist."TRX余额：<code>". (empty($res['data'][0]['balance']) ? 0 : calculationExcept($res['data'][0]['balance'],6)) ."</code>\n";
        
                            if(!empty($res['data'][0]['trc20'])){
                                for($i=1; $i<=count($res['data'][0]['trc20']); $i++){
                                    if(!empty($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'])){
                                        $returnlist = $returnlist."USDT余额：<code>". (calculationExcept($res['data'][0]['trc20'][$i-1]['TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'],6)) ."</code>\n";
                                        break;
                                    }
                                }
                            }
                            
                            //转账和资源
                            $returnlist = $returnlist."创建时间：". (isset($res['data'][0]['create_time']) ?date('Y-m-d H:i:s',$res['data'][0]['create_time']/1000):'-') ."\n";
                            $returnlist = $returnlist."最后活跃：". (isset($res['data'][0]['latest_opration_time']) ?date('Y-m-d H:i:s',$res['data'][0]['latest_opration_time']/1000):'-') ."\n";
                            
                            //查询所有者权限
                            if(isset($res['data'][0]['owner_permission'])){
                                $ownerPermission = $res['data'][0]['owner_permission'];
                                $returnlist = $returnlist . "\n🟠🟠所有权限-阈值：".$ownerPermission['threshold']."🟠🟠\n";
                                $ownerPermissionList = '';
                                for($i=0;$i<count($ownerPermission['keys']);$i++){
                                    $ownerBen = $ownerPermission['keys'][$i]['address'] == $message ?"本地址":"其他地址";
                                    $ownerPermissionList = $ownerPermissionList."<b>".$ownerBen."</b>：<code>".$ownerPermission['keys'][$i]['address']."</code> (权重：".$ownerPermission['keys'][$i]['weight'].")\n";
                                }
                                $returnlist = $returnlist.$ownerPermissionList;
                            }
                            
                            //查询活跃权限
                            if(isset($res['data'][0]['active_permission'])){
                                $activePermissions = $res['data'][0]['active_permission'];
                                if(count($activePermissions) > 0){
                                    $returnlist = $returnlist . "\n🔴🔴活跃权限-共：".count($activePermissions)."个🔴🔴\n";
                                    for($i=0;$i<count($activePermissions);$i++){
                                        $returnlist = $returnlist . "第". ($i+1) ."个-权限名称：".$activePermissions[$i]['permission_name']." 权限ID：".$activePermissions[$i]['id']." 权限阈值：".$activePermissions[$i]['threshold']."\n";
                                        $activePermissionList = '';
                                        for($j=0;$j<count($activePermissions[$i]['keys']);$j++){
                                            $activeBen = $activePermissions[$i]['keys'][$j]['address'] == $message ?"本地址":"其他地址";
                                            $activePermissionList = $activePermissionList."<b>".$activeBen."</b>：<code>".$activePermissions[$i]['keys'][$j]['address']."</code> (权重：".$activePermissions[$i]['keys'][$j]['weight'].")\n";
                                        }
                                        $returnlist = $returnlist.$activePermissionList;
                                    }
                                }
                            }
                        }
                        
                    }else{
                        $returnlist =  '您的地址:'.$message.'，获取余额失败3，检查钱包地址是否正确';
                    }
                }
            }
            return $return.$returnlist;
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 查询以太余额-nodejs
    private function queryercbalance($message)
    {
        #异常处理
        try {
            $AipHttpClient = new AipHttpClient();
        
            $apiWebUrl = config('services.api_web.url');
            $balance_url = $apiWebUrl . '/api/erc/addressgetbalance';      //查地址
            $params = [
                'address' => $message
            ];
            $res = $AipHttpClient->postnew($balance_url,$params);

            if(empty($res)){
                $replytext = '查地址：'.$message.' 获取余额失败1，检查地址';
            }else{
                $res = json_decode($res,true);
                if(empty($res['code'])){
                    $replytext = '查地址：'.$message.' 获取余额失败2，检查地址';
                }else{
                    if($res['code'] == 200){
                        $replytext = '查询：`'.$message.'`'.PHP_EOL
                        .'查询时间：'.now().PHP_EOL.PHP_EOL
                        .'✅✅✅✅查询结果✅✅✅✅'.PHP_EOL.PHP_EOL
                        .'以太ETH余额：'.$res['data']['ethbalance'].PHP_EOL
                        .'以太USDT余额：'.$res['data']['ethusdtbalance'].PHP_EOL.PHP_EOL
                        .'币安BNB余额：'.$res['data']['bnbbalance'].PHP_EOL
                        .'币安USDT余额：'.$res['data']['bnbusdtbalance'].PHP_EOL.PHP_EOL
                        .'欧易OKT余额：'.$res['data']['okxbalance'].PHP_EOL
                        .'欧易USDT余额：'.$res['data']['okxusdtbalance'];
                    }else{
                        $replytext = '查地址:'.$message.' 获取余额失败3，检查地址';
                    }
                }
            }
            return $replytext;
            
        } catch (\Throwable $e) {
            return '';
        }
    }
    
    // 查询欧意c2c价格
    private function queryokxc2c($message)
    {
        #异常处理
        try {
            if($message == 'z1' || $message == 'Z1'){
                $paymentMethod = 'bank';
                $queryName = '银行卡';
            }elseif($message == 'z2' || $message == 'Z2'){
                $paymentMethod = 'aliPay';
                $queryName = '支付宝';
            }elseif($message == 'z3' || $message == 'Z3'){
                $paymentMethod = 'wxPay';
                $queryName = '微信';
            }else{
                $paymentMethod = 'all';
                $queryName = '所有';
            }
            $url = 'https://www.okx.com/v3/c2c/tradingOrders/books?t=1692610833730&quoteCurrency=cny&baseCurrency=usdt&side=sell&paymentMethod='.$paymentMethod.'&userType=all&receivingAds=false';
            $res = Get_Curl($url,'',[],5); //5秒超时
            
            if(empty($res)){
                $returnlist = "查询结果：查询失败1\n"."查询时间：".nowDate();
            }else{
                $res = json_decode($res,true);
                if($res['code'] == 0){
                    $returnlist = "<b>[Okex商家C2C实时top10 - ".$queryName."]</b>\n"."<b>查询时间：<code>".nowDate()."</code></b>\n\n";
                    $ico = array('0'=>'1️⃣','1'=>'2️⃣','2'=>'3️⃣' ,'3'=>'4️⃣','4'=>'5️⃣' ,'5'=>'6️⃣','6'=>'7️⃣','7'=>'8️⃣' ,'8'=>'9️⃣'  ,'9'=>'🔟' );
                    
                    for($i=0; $i<10; $i++) {
                        $paymentMethods = $res['data']['sell'][$i]['paymentMethods'];
                        $paymentMethodsName = '';
                        if(in_array('bank', $paymentMethods)){
                            $paymentMethodsName = $paymentMethodsName.'银行卡 ';
                        }
                        if(in_array('aliPay', $paymentMethods)){
                            $paymentMethodsName = $paymentMethodsName.'支付宝 ';
                        }
                        if(in_array('wxPay', $paymentMethods)){
                            $paymentMethodsName = $paymentMethodsName.'微信';
                        }
                        
                        $returnlist = $returnlist
                                    .$ico[$i]."  <code>".$res['data']['sell'][$i]['price']."  ".$res['data']['sell'][$i]['nickName']. "</code>  🈶" .$paymentMethodsName. "\n";
                    }
                }else{
                    $returnlist = "查询结果：查询失败2\n"."查询时间：".nowDate();
                }
            }
            return $returnlist;
            
        } catch (\Throwable $e) {
            return '';
        }
    }
}
