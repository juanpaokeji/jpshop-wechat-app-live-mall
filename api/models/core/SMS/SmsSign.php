<?php
/**
 * This file is part of JWT.
 *
 */

namespace app\models\core\SMS;

use Qcloud\Sms\SmsSenderUtil;


/**
 * 腾讯云短信签名
 *
 * @version   2018年03月29日
 * @author    JYS <272074691@qq.com>
 * @copyright Copyright 2010-2016 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class SmsSign{
    private $url;
    private $appid;
    private $appkey;
    private $util;

    function __construct($appid, $appkey){
            $this->url = [
                    "add" =>"https://yun.tim.qq.com/v5/tlssmssvr/add_sign",
                    "mod" =>"https://yun.tim.qq.com/v5/tlssmssvr/mod_sign",
                    "del" =>"https://yun.tim.qq.com/v5/tlssmssvr/del_sign",
                    "get" =>"https://yun.tim.qq.com/v5/tlssmssvr/get_sign"
                    ];
            $this->appid =  $appid;
            $this->appkey = $appkey;
            $this->util = new SmsSenderUtil();
        }
    /**
     * 添加签名
     * @param  [string]  $text   签名内容，不带【】，例如：【腾讯科技】这个签名，这里填"腾讯科技"
     * @param  string  $pic
     * @param  string  $remark 签名备注，比如申请原因，使用场景等，可选字段
     * @return string json string { "result": xxxxx, "msg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    public function addSign($text,  $pic = "", $remark = "")
    {
    	$random       = $this->util->getRandom();
        $curTime      = time();
        $wholeUrl     = $this->url['add'] . "?sdkappid=" . $this->appid . "&random=" . $random;
        $data         = new \stdClass();
        $data->time   = $curTime;
        $data->remark = $remark;
        $data->text   = $text;
        $data->sig    = hash("sha256", "appkey=" . $this->appkey . "&random=" . $random . "&time=" . $data->time);
        $data->pic    = $this->imgToBase64($pic);
        return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    /**
     * 修改签名
     * @param  string $remark  新的签名备注，比如申请原因，使用场景等，可选字段
     * @param  string $text    新的签名内容，不带【】，例如：改为【腾讯科技】这个签名，这里填"腾讯科技"
     * @param  string $pic  新的签名备注，比如申请原因，使用场景等，可选字段
     * @param  integer $sign_id 待修改的签名对应的签名id
     * @return string json string { "result": xxxxx, "msg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    public function modSign($sign_id, $text, $pic = "", $remark = "")
    {
        	$random        = $this->util->getRandom();
            $curTime       = time();
            $wholeUrl      = $this->url['mod'] . "?sdkappid=" . $this->appid . "&random=" . $random;
            $data          = new \stdClass();
            //$data->tel   = $this->util->phoneNumbersToArray($nationCode, $phoneNumbers);
            $data->time    = $curTime;
            $data->remark  = $remark;
            $data->text    = $text;
            $data->sign_id = (int)$sign_id;
            $data->sig     = hash("sha256","appkey=" . $this->appkey . "&random=" . $random . "&time=" . $data->time);
            $data->pic     = $this->imgToBase64($pic);
            return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    /**
     * 删除签名
     * @param array|int $sign_ids 签名id，也可以通过值指定一个"sign_id":123
     * @return string json string { "result": xxxxx, "msg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    public function delSign($sign_ids)
    {
        	$random = $this->util->getRandom();
            $curTime = time();
            $wholeUrl = $this->url['del'] . "?sdkappid=" . $this->appid . "&random=" . $random;
            $data = new \stdClass();
            $data->time = $curTime;
            $data->sign_id = (array)$sign_ids;
            $data->sig = hash("sha256","appkey=" . $this->appkey . "&random=" . $random . "&time=" . $data->time);
            return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    /**
     * 签名状态查询
     * @param  array $sign_ids 签名id，也可以通过值指定一个"sign_id":123
     * @return string json string { "result": xxxxx, "msg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    public function pullSignStatus($sign_ids)
    {
        	$random = $this->util->getRandom();
            $curTime = time();
            $wholeUrl = $this->url['get'] . "?sdkappid=" . $this->appid . "&random=" . $random;
            $data = new \stdClass();
            $data->time = $curTime;
            $data->sig = hash("sha256", "appkey=" . $this->appkey . "&random=" . $random . "&time=" . $data->time);
            $data->sign_id = (array)$sign_ids;
            return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    public function imgToBase64($img_file) {
            $img_base64 = '';
            if (file_exists($img_file)) {
                    $img_info = getimagesize($img_file); // 取得图片的大小，类型等

                    if ($img_info[2] === 2 || $img_info[2] === 3) {
                            $fp = fopen($img_file, "r"); // 图片是否可读权限
                            if ($fp) {
                                    $filesize = filesize($img_file);
                                    $content = fread($fp, $filesize);
                                    $img_base64 = chunk_split(base64_encode($content));
                                }
                fclose($fp);
            }
        }

        return $img_base64; //返回图片的base64
    }
}
