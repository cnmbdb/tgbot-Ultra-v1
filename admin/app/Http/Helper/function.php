<?php

use Illuminate\Support\Facades\Redis;

if (!function_exists('res_422')) {

    function res_422($msg='参数有误！'){
        return json_encode(['code' => 422, 'data' => [], 'count' => 0,'msg'=>$msg]);
    }
}

//这个只能用在方法末尾
if (!function_exists('opt_return')){
    function opt_return($res,$msg=null){
        if (!$res) {
            if (!$msg){
                $msg = '操作失败';
            }
            die(json_encode(array('status' => 2, 'message' => $msg)));
        }
        die(json_encode(array('status' => 1, 'message' => '操作成功')));
    }
}

if (!function_exists('opt_ok')){
    function opt_ok(){
        die(json_encode(array('status' => 1, 'message' => '操作成功'),JSON_UNESCAPED_UNICODE));
    }
}

if (!function_exists('opt_error')){
    function opt_error($msg=null){
        if (!empty($msg)){
            die(json_encode(array('status' => 2, 'message' => $msg)));
        }
        die(json_encode(array('status' => 2, 'message' => '操作失败')));
    }
}

if (!function_exists('table_error')){
    function table_error($msg=null){
        if (!$msg)
            $msg = '数据查询异常，请检查参数！';
        die(json_encode(['code'=>-1,'msg'=>$msg]));
    }
}

if (!function_exists('format_time')){
    function format_time(&$param,$format){
        if (isset($param['stime']))
            $param['stime'][1] = date($format,$param['stime'][1]);
        if (isset($param['etime']))
            $param['etime'][1] = date($format,$param['etime'][1]+86400);
    }
}

function nextDay($data)
{
    return date('Y-m-d',strtotime('+1 day', strtotime($data)));
}

function nowDate()
{
    return date('Y-m-d H:i:s');
}

if (!function_exists('getTree')){
    function getTree($data, $pId=0)
    {
        $tree = array();
        foreach ($data as $k => $v) {
            if ($v->parentId == $pId) { //父亲找到儿子
                $v->children = getTree($data, $v->id);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }
}

/**
    * 把数字1-1亿换成汉字表述，如：123->一百二十三
    * @param [num] $num [数字]
    * @return [string] [string]
    */
if (!function_exists('numToWord')) {
    function numToWord($num)
    {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');
 
        $chiStr = '';
 
        $num_str = (string)$num;
 
        $count = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num = null; //临时数字
 
        $chiStr = '';//拼接结果
        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num]; 
        }else if($count > 2){
            $index = 0;
            for ($i=$count-1; $i >= 0 ; $i--) { 
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag ) {
                        $chiStr = $chiNum[$temp_num]. $chiStr;
                        $last_flag = true;
                    }
                }else{
                    $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;
                    $zero_flag = false;
                    $last_flag = false;
                }
                $index ++;
            }
        }else{
            $chiStr = $chiNum[$num_str[0]]; 
        }
        return $chiStr;
    }
}

if (!function_exists('createNo')) {
    function createNo($prefix = '')
    {
        return $prefix . date('YmdHis') . sprintf("%03d", rand(1, 99));
    }
}


if (!function_exists('llog')) {
    function llog($content, $channel = '', $level = 'info')
    {
        if ($channel == '') {
            \Log::info($content);
        } else {
            switch ($level) {
                case 'info':
                    \Log::channel($channel)->info($content);
                    break;
                case 'error':
                    \Log::channel($channel)->error($content);
                    break;
            }
        }
       
    }
}
    
if (!function_exists('complete_pic')) {
    function complete_pic($val) {
        // $url = $remote == 'server' ? env('APP_URL') : env('API_URL');
        if ($val != '' && strpos($val, 'http') === false) {
            return env('OSS_URL') . $val;
        }
        return $val;
    }
}

/**
 * API请求
 * @param $url  链接
 * @param $data  参数
 */
function Get_Curl($url, $data = null, array $heders = [], $time=6)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $time);          //单位 秒，也可以使用
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //这个是重点,规避ssl的证书检查。
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 跳过host验证
    if(!empty($data)){
        // 如果是数组，转换为JSON格式（API-web需要JSON格式）
        if(is_array($data)){
            $data = json_encode($data);
            $heders[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    if(!empty($heders)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $heders);
    }
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

/**
 * API请求2
 * @param $url  链接
 * @param $data  参数
 */
function curl_post_https($url,$data,$headers=null,$cookie=null){ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    if(!empty($headers)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);//设置请求头
    }
    if(!empty($cookie)){
        curl_setopt($curl, CURLOPT_COOKIE, $cookie); // 带上COOKIE请求
    } 
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据
}

/**
 * API请求3
 * @param $url  链接
 * @param $data  参数
 */
function curl_get_https($url,$headers=null,$raw=null,$time=6){
    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, $time);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    if(!empty($headers)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);//设置请求头
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
    if($raw){
        curl_setopt($curl, CURLOPT_POSTFIELDS, $raw); // Post提交的数据包 
    }
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    curl_close($curl);
    return $tmpInfo;   
}

/**
 * post请求
 * @param $url  string api链接
 * @param $data  array 参数
 * @param $time_out int 超时时间
 */
function post_multi(string $url, array $data = [],$time_out = 5)
{
    $headerArray = array("Content-Type: multipart/form-data;charset='utf-8'");
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT , $time_out);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return json_decode($output,true);
}

/**
 * 转为十进制
 * @param  string $number 十六进制的数
 * @return string
 */
function hexToDec($number)
{
    // 如果有0x去除它
    $number = remove0x(strtolower($number));
    $decvalues = [
        '0' => '0', '1' => '1', '2' => '2',
        '3' => '3', '4' => '4', '5' => '5',
        '6' => '6', '7' => '7', '8' => '8',
        '9' => '9', 'a' => '10', 'b' => '11',
        'c' => '12', 'd' => '13', 'e' => '14',
        'f' => '15'];
    $decval = '0';
    $number = strrev($number);
    for($i = 0; $i < strlen($number); $i++) {
        $decval = bcadd(bcmul(bcpow('16', $i, 0), $decvalues[$number[$i]]), $decval);
    }
    return $decval;
}

/**
 * 如果有0x去除它
 * @param $value
 * @return false|string
 */
function remove0x($value)
{
    if (strtolower(substr($value, 0, 2)) == '0x') {
        return substr($value, 2);
    }
    return $value;
}
/**
 * 高精度计算相除
 * @param $price [金额]
 * @param $number [多少个0]
*/
function calculationExcept($price,$number){
    $multiple = 1;          //倍数
    if($number > 0){
        for ($i=0; $i < $number; $i++) { 
            $multiple = $multiple.'0';
        }
        $multiple = (int)$multiple;
    }
    $data = bcdiv($price,$multiple,$number);

    if(strpos(floatval($data),'-') == false){
        // 精度未失效
        $data = floatval($data);
    }else{
        // 精度失效
        $data = rtrim(rtrim($data, '0'), '.');
    }

    return $data;
}

/**
 * 获取Redis
 * @param $field  字段
 */
function getRedis($field){
    $prefix = env('REDIS_PREFIX','sdadmin_');
    return Redis::get($prefix.$field);
}

/**
 * 存储Redis
 * @param $field  字段
 * @param $data  值
 */
function setRedis($field,$data){
    $prefix = env('REDIS_PREFIX','sdadmin_');
    return Redis::set($prefix.$field,$data);
}

/**
 * 存储Redis,含有效期
 * @param $field  字段
 * @param $data  值
 * @param $expireSeconds  有效期,单位秒
 */
function setexRedis($field,$data,$expireSeconds = 60){
    $prefix = env('REDIS_PREFIX','sdadmin_');
    return Redis::setex($prefix.$field,$expireSeconds,$data);
}

/**
 * 删除Redis
 * @param $data  值
 */
function deleteRedis($data){
    $prefix = env('REDIS_PREFIX','sdadmin_');
    return Redis::del($prefix.$data);
}

/**
 * 获取Redis数组key
 * @param $data  值
 */
function getKeyRedis(){
    return Redis::keys("_*");
}

/**
 * 获取真实IP
 * @param 
 * @return string
 */
function getClientIP()
{
    $ip_address = '';
    if(getenv('HTTP_CLIENT_IP'))
        $ip_address = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ip_address = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ip_address = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ip_address = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ip_address = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ip_address = getenv('REMOTE_ADDR');
    else
        $ip_address = 'UNKNOWN';
    return $ip_address;
}

