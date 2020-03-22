<?php

function msectime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

//标准失败输出
function error($msg = '', $list = [], $code = 400) {
    return [
        'status' => $code,
        'message' => $msg,
        'data' => $list,
    ];
}

//标准成功输出
function success($msg, $list = [], $code = 200) {
    return [
        'status' => $code,
        'message' => $msg,
        'data' => $list,
    ];
}
//标准化输出2
function result2($status, $message, $data = [],$count = '',$ext_msg='') {
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $result = [
        'code' => $status,
        'message' => $message,
        'ext_msg'=>$ext_msg
    ];
    empty($data) ? $data : $result['body']=$data;
    empty($count) ? $count : $result['count']=$count;
    return $result;
}
//标准化输出
function result($status, $message, $data = '') {
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $result = [
        'status' => $status,
        'message' => $message,
    ];
    $data != "" ? $result['data'] = $data : $data;
    return $result;
}

//获取请求方法 method 及请求数据 params
function request() {
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $request['method'] = Yii::$app->request->method;
    $request['method'] == 'GET' ? $request['params'] = Yii::$app->request->queryParams : $request['params'] = array_merge(Yii::$app->request->queryParams, Yii::$app->request->bodyParams);
    return $request;
}

/** [isDeployStatus 是否是测试环境] */
function isDeployStatus() {
    return defined('AXAPI_DEPLOY_STATUS') && AXAPI_DEPLOY_STATUS === 1;
}

/**
 * [getArrPageUrl 获取URL参数]
 * @param  string $pageUrl [description]
 * @return [type]          [description]
 */
function getArrPageUrl($pageUrl = '') {
    $arrQuery = [];
    $arrPageUrl = parse_url($pageUrl);
    if ($arrPageUrl['query']) {
        parse_str($arrPageUrl['query'], $arrQuery);
    }
    return $arrQuery;
}

// 获取生日对应的年龄
function birthday($birthday = 0) {
    $age = strtotime($birthday);
    if ($age === false) {
        return 0;
    }
    list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
    $now = strtotime("now");
    list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
    $age = $y2 - $y1;
    if ((int) ($m2 . $d2) < (int) ($m1 . $d1))
        $age -= 1;
    return $age;
}

function getTimeLabel($time = 0, $format = 'Y-m-d H:i') {
    $label = '';
    if ($time > 0) {
        $time = strtotime($time);
        $label = date($format, $time);
    }
    return $label;
}

function getTimeNow() {
    return date('Y-m-d H:i:s');
}

/**
 * [jsonDecode description]
 * @param  string $json [description]
 * @param  boolean $isArray [description]
 * @return [type]           [description]
 */
function jsonDecode($json = '', $isArray = true) {
    $array = json_decode($json, $isArray);
    return $array;
}

/** 字符串转换。驼峰式字符串（首字母小写） */
function camelCase($str) {
    //使用空格隔开后，每个单词首字母大写
    $str = ucwords(str_replace('_', ' ', $str));
    //小写字符串的首字母，然后删除空格
    $str = str_replace(' ', '', lcfirst($str));
    $str = str_replace('Id', 'ID', $str);
    return $str;
}

/** 字符串转换。驼峰转换成下划线的形式 */
function under_score($str) {
    $str = str_replace('ID', 'Id', $str);
    return strtolower(ltrim(preg_replace_callback('/[A-Z]/', function ($mathes) {
                        return '_' . strtolower($mathes[0]);
                    }, $str), '_'));
}

/**
 * 将用户和登陆时间组成加密字符
 * @param  integer $p_userID 用户ID
 * @param  string $p_time 时间戳
 * @return string            加密后字符
 */
function getsalt($p_userID, $p_userName, $p_time) {
    return md5(md5($p_userName) . md5($p_time)) . '///' . $p_userID . '///' . $p_userName . '///' . $p_time . '///' . md5(md5($p_userID) . md5($p_time));
}

//建立以年月日为文件夹名
function creat_mulu($str) {
    $val = explode('/',$str);
    $a = "";
    for($i=0;$i<count($val);$i++){
        if($i==0){
            creatFolder($val[0]);
            $a = $val[0];
        }else{
            $a = $a."/".$val[$i];
            creatFolder($a);
        }
    }
    creatFolder($str);
    creatFolder($str . "/" . date('Y'));
    creatFolder($str . "/" . date('Y') . "/" . date('m'));
    creatFolder($str . "/" . date('Y') . "/" . date('m') . "/" . date('d'));
    return $str . "/" . date('Y') . "/" . date('m') . "/" . date('d');
}

function creat_mulu1($str) {
    $val = explode('/',$str);
    $a = "";
    for($i=0;$i<count($val);$i++){
        if($i==0){
            creatFolder($val[0]);
            $a = $val[0];
        }else{
            $a = $a."/".$val[$i];
            creatFolder($a);
        }
    }
    creatFolder($str);
}

//如果文件夹不存在则创建文件夹
function creatFolder($f_path) {
    if (!file_exists($f_path)) {
        mkdir($f_path, 0777);
    }
}

function getConfig($key) {
    $key  = $key."-".$_SERVER['HTTP_HOST'];
    $value = \Yii::$app->redis->get($key);

    if ($value) {
        $value = json_decode($value, true);
    } else {
        $value = (new \yii\db\Query())->select(['value'])->from('system_config')->where(['key' => $key])->one();
        if ($value) {
            setConfig($key, $value['value']);
            $value = json_decode($value['value'], true);
        }
    }
    return $value;
}

function reidsAll(){
    \Yii::$app->redis->flushall();
}

function getRedis($key) {
    $key  = $key."-".$_SERVER['HTTP_HOST'];
    $value = \Yii::$app->redis->get($key);

    if ($value) {
        $value = json_decode($value, true);
    }
    return $value;
}

function setConfig($key, $value = "", $time = 0) {
    $key = $key."-".$_SERVER['HTTP_HOST'];
    $value ? $result = \yii::$app->redis->set($key, json_encode($value)) : $result = \yii::$app->redis->del($key);
    if ($time != 0) { 
        \yii::$app->redis->expire($key, $time);
    }
    return $result;
}

/**
 * 获取触发条件 路由
 * @return string
 */
function getCondition() {
    $str = Yii::$app->controller->id;
    $str .= '/' . Yii::$app->controller->action->id;
    $request = request(); //获取 request 对象 及方法
    $str .= '/' . strtolower($request['method']);
    $str = strtr($str, '/', '_');
    return $str;
}

function generate($length = 6) {
// 密码字符集，可任意添加你需要的字符 
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = "";
    for ($i = 0; $i < $length; $i++) {
// 这里提供两种字符获取方式 
// 第一种是使用 substr 截取$chars中的任意一位字符； 
// 第二种是取字符数组 $chars 的任意元素 
// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1); 
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}

function generateCode($length = 6) {
// 密码字符集，可任意添加你需要的字符 
    $chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = "";
    for ($i = 0; $i < $length; $i++) {
// 这里提供两种字符获取方式 
// 第一种是使用 substr 截取$chars中的任意一位字符； 
// 第二种是取字符数组 $chars 的任意元素 
// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1); 
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}

function score($merchant_id, $key, $userid, $condition) {
    $rule = (new \yii\db\ActiveRecord())->select()->from('forum_score_rule')->where(['merchant_id' => $merchant_id, '`key`' => $key, 'condition' => $condition])->one();
    $user = (new \yii\db\ActiveRecord())->select()->from('forum_user')->where(['id' => $userid, 'merchant_id' => $merchant_id, '`key`' => $key])->one();
    $value = (new \yii\db\ActiveRecord())->update('forum_user', ['score' => $user['score'] + $rule['score']], ['id' => $userid, 'merchant_id' => $merchant_id, '`key`' => $key]);
    $data = array(
        'merchant_id' => $merchant_id,
        '`key`' => $key,
        'user_id' => $userid,
        'score' => $rule['score'],
        'content' => $condition,
        'type' => $condition,
        'status' => 1,
        'create_time' => time()
    );
    $rs = (new \yii\db\ActiveRecord())->save('forum_user_score', $data);
    return $rs;
}

function save_file($data) {

    $fp = fopen(\Yii::getAlias('@webroot/') . "public/design/font.css", 'w');
    fwrite($fp, $data);
    fclose($fp);
}

/**
 * curl get 请求
 * @param string $url 请求地址
 * @return array
 */
function curlGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!curl_exec($ch)) {
        $data = '';
    } else {
        $data = curl_multi_getcontent($ch);
    }
    curl_close($ch);
    return $data;
}

/**
 * curl post 请求
 * @param string $url 请求地址
 * @param string $data 请求参数
 * @return array
 */
function curlPost($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    if (!curl_exec($ch)) {
        $data = '';
    } else {
        $data = curl_multi_getcontent($ch);
    }
    curl_close($ch);
    return $data;
}

/**
 * curl post 请求
 * @param string $url 请求地址
 * @param string $data 请求参数
 * @return array
 */
function curlPostJson($url, $data) {
    $header[] = 'Content-Type:application/json;charset=utf-8';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    if (!curl_exec($ch)) {
        $data = '';
    } else {
        $data = curl_multi_getcontent($ch);
    }
    curl_close($ch);
    return $data;
}

function agent() {
    //微信浏览器
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    } else {
        return false;
    }
}

function order_sn() {
    $order_id = date('YmdHis') . str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    return $order_id;
}

function logistics($nu, $com) {
    $redis = getConfig($nu);
    if (!$redis) {
        $requestData = "{'OrderCode':'','ShipperCode':'{$com}','LogisticCode':'{$nu}'}";

        $datas = array(
            'EBusinessID' => "1333816",
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = ems_encrypt($requestData, "cb26aafb-f391-4af7-8339-44aeec1c7453");
        $data = sendPost("http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx", $datas);

        //根据公司业务处理返回的信息......

        $data = json_decode($data, true);

        if ($data['State'] == 3) {
            setConfig($nu, $data);
            yii::$app->redis->expire($nu, 2592000);
        } else {
            setConfig($nu, $data);
            yii::$app->redis->expire($nu, 7200);
        }
        return $data;
    } else {
        return $redis;
    }
}

/**
 *  post提交数据 
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据 
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
    $temps = array();
    foreach ($datas as $key => $value) {
        $temps[] = sprintf('%s=%s', $key, $value);
    }
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
    if (empty($url_info['port'])) {
        $url_info['port'] = 80;
    }
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader .= "Host:" . $url_info['host'] . "\r\n";
    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader .= "Connection:close\r\n\r\n";
    $httpheader .= $post_data;
    $fd = fsockopen($url_info['host'], $url_info['port']);
    fwrite($fd, $httpheader);
    $gets = "";
    $headerFlag = true;
    while (!feof($fd)) {
        if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
            break;
        }
    }
    while (!feof($fd)) {
        $gets .= fread($fd, 128);
    }
    fclose($fd);

    return $gets;
}

/**
 * 电商Sign签名生成
 * @param data 内容   
 * @param appkey Appkey
 * @return DataSign签名
 */
function ems_encrypt($data, $appkey) {
    return urlencode(base64_encode(md5($data . $appkey)));
}

function html2imgs($html) {
    $imgs = array();
    if (empty($html))
        return $imgs;

    preg_match_all("/<img[^>]+>/i", $html, $img);

    if (empty($img))
        return $imgs;
    $img = $img[0];

    foreach ($img as $g) {
        $g = preg_replace("/^<img|>$/i", '', $g); //移除二头字符
        preg_match("/\ssrc\s*\=\s*\"([^\"]+)|\ssrc\s*\=\s*'([^']+)|\ssrc\s*\=\s*([^\"'\s]+)/i", $g, $src); //空格 src 可能空格 = 可能空格 "非"" 或 '非'' 或 非空白 这几种可能,下同 
        $src = empty($src) ? '' : $src[count($src) - 1]; //匹配到,总会放在最后

        if (empty($src)) {//空的src? 没用,跳过
            continue;
        }

        preg_match("/\salt\s*\=\s*\"([^\"]+)|\salt\s*\=\s*'([^']+)|\salt\s*\=\s*(\S+)/i", $g, $alt);
        $alt = empty($alt) ? $src : $alt[count($alt) - 1]; //alt没值?用src
        preg_match("/\stitle\s*\=\s*\"([^\"]+)|\stitle\s*\=\s*'([^']+)|\stitle\s*\=\s*(\S+)/i", $g, $title);
        $title = empty($title) ? $src : $title[count($title) - 1]; //title没值?用src
        $imgs[] = $src;
    }
    return $imgs;
}

//unicode 转中文
function unicodeDecode($unicode_str) {
    $json = '{"str":"' . $unicode_str . '"}';
    $arr = json_decode($json, true);
    if (empty($arr))
        return '';
    return $arr['str'];
}

// 中文转unicode
function UnicodeEncode($str) {
    preg_match_all('/./u', $str, $matches);
    $unicodeStr = "";
    foreach ($matches[0] as $m) {
        //拼接
        $unicodeStr .= "&#" . base_convert(bin2hex(iconv('UTF-8', "UCS-4", $m)), 16, 10);
    }
    return $unicodeStr;
}

function secToTime($times) {
    $result = '00:00:00';
    if ($times > 0) {
        $hour = floor($times / 3600);
        $minute = floor(($times - 3600 * $hour) / 60);
        $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);



        $hour = strlen($hour) == 1 ? '0' . $hour : $hour;
        $minute = strlen($minute) == 1 ? '0' . $minute : $minute;
        $second = strlen($second) == 1 ? '0' . $second : $second;

        $result = $hour . ':' . $minute . ':' . $second;
    }
    return $result;
}

function electronics($eorder, $sender, $receiver, $commodity) {
    //构造电子面单提交信息
//        $eorder = [];
//        $eorder["MemberID"] = "123456";
//        $eorder["ShipperCode"] = "ZTO";
//        $eorder["LogisticCode"] = "1234561";
//        $eorder["ThrOrderCode"] = "1234567890";
//        $eorder["OrderCode"] = "1234561";
//        $eorder['IsReturnTemp'] = 1;
//        $eorder["CustomerName"] = "admin";
//        $eorder["CustomerPwd"] = "kdniao";
//        $eorder["SendSite"] = "福田保税区网点";
//        $eorder = [];
//        $eorder["ShipperCode"] = "SF";
//        $eorder["OrderCode"] = time();
//        $eorder["PayType"] = 1;
//        $eorder["ExpType"] = 1;
//        $eorder['IsReturnPrintTemplate'] = 1;
//
//
//        $sender = [];
//        $sender["Name"] = "李先生";
//        $sender["Mobile"] = "18888888888";
//        $sender["ProvinceName"] = "李先生";
//        $sender["CityName"] = "深圳市";
//        $sender["ExpAreaName"] = "福田区";
//        $sender["Address"] = "赛格广场5401AB";
//
//        $receiver = [];
//        $receiver["Name"] = "李先生";
//        $receiver["Mobile"] = "18888888888";
//        $receiver["ProvinceName"] = "李先生";
//        $receiver["CityName"] = "深圳市";
//        $receiver["ExpAreaName"] = "福田区";
//        $receiver["Address"] = "赛格广场5401AB";
//
//        $commodityOne = [];
//        $commodityOne["GoodsName"] = "其他";
//        $commodity = [];
//        $commodity[] = $commodityOne;

    $eorder["Sender"] = $sender;
    $eorder["Receiver"] = $receiver;
    $eorder["Commodity"] = $commodity;


//调用电子面单
    $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);
    //  echo "电子面单接口提交内容：<br/>" . $jsonParam;
    $jsonResult = submitEOrder($jsonParam);
    //    echo "<br/><br/>电子面单提交结果:<br/>" . $jsonResult;
//解析电子面单返回结果
    $result = json_decode($jsonResult, true);
    return $result;
}

/**
 * Json方式 调用电子面单接口
 */
function submitEOrder($requestData) {
    $datas = array(
        'EBusinessID' => 'test1333816',
        'RequestType' => '1007',
        'RequestData' => urlencode($requestData),
        'DataType' => '2',
    );
    $datas['DataSign'] = ems_encrypt($requestData, "87fcae6b-e5fc-4a3c-a7c9-69c66452d438");
    $result = sendPost("http://sandboxapi.kdniao.com:8080/kdniaosandbox/gateway/exterfaceInvoke.json", $datas);

    //根据公司业务处理返回的信息......

    return $result;
}

/* * ************************************************************ 
 * 
 *  使用特定function对数组中所有元素做处理 
 *  @param  string  &$array     要处理的字符串 
 *  @param  string  $function   要执行的函数 
 *  @return boolean $apply_to_keys_also     是否也应用到key上 
 *  @access public 
 * 
 * *********************************************************** */

function arrayRecursive(&$array, $function, $apply_to_keys_also = false) {
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
}

/* * ************************************************************ 
 * 
 *  将数组转换为JSON字符串（兼容中文） 
 *  @param  array   $array      要转换的数组 
 *  @return string      转换得到的json字符串 
 *  @access public 
 * 
 * *********************************************************** */

function JSON($array) {
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}

//获取毫秒级时间戳
function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

//function redirect($url, $terminate = true, $statusCode = 302) {
//    if (is_array($url)) {
//        $route = isset($url[0]) ? $url[0] : '';
//        $url = $this->createUrl($route, array_splice($url, 1));
//    }
//
//    Yii::app()->getRequest()->redirect($url, $terminate, $statusCode);
//}

function xzphp_curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 3);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $i = 0;
    do {
        $output = curl_exec($ch);
    } while (!$output && ++$i < 3);
    curl_close($ch);
    return $output;
}


