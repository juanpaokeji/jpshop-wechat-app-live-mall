<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/30
 * Time: 9:18
 */

namespace app\models\core\SMS;

use Qcloud\Sms\SmsSenderUtil;

class SendStatusPuller
{
    private $url;
    private $appid;
    private $appkey;
    private $util;

    /**
     * 构造函数
     *
     * @param string $appid  sdkappid
     * @param string $appkey sdkappid对应的appkey
     */
    public function __construct($appid, $appkey)
    {
        $this->url = [
            "send" =>"https://yun.tim.qq.com/v5/tlssmssvr/pullsendstatus",
            "reply" =>"https://yun.tim.qq.com/v5/tlssmssvr/pullcallbackstatus",
        ];
        $this->appid =  $appid;
        $this->appkey = $appkey;
        $this->util = new SmsSenderUtil();
    }

    /**
     * 拉取回执结果
     *
     * @param string $url 拉取类型，0表示回执结果，1表示回复信息
     * @param string $begin_date 拉取类型，0表示回执结果，1表示回复信息
     * @param string $end_date  最大条数，最多100
     * @return string 应答json字符串，详细内容参见腾讯云协议文档
     */
    private function pull($url,$begin_date, $end_date)
    {
        $random = $this->util->getRandom();
        $curTime = time();
        $wholeUrl = $url . "?sdkappid=" . $this->appid . "&random=" . $random;

        $data = new \stdClass();
        $data->begin_date = $begin_date;
        $data->end_date = $end_date;
        $data->sig = $this->util->calculateSigForPuller($this->appkey, $random, $curTime);
        $data->time = $curTime;
        return $this->util->sendCurlPost($wholeUrl, (array)$data);
    }

    /**
     * 拉取回执结果
     *
     * @param int $begin_date 开始时间，yyyymmddhh 需要拉取的起始时间,精确到小时
     * @param int $end_date   结束时间，yyyymmddhh 需要拉取的截止时间,精确到小时
     * @return string 应答json字符串，详细内容参见腾讯云协议文档
     */
    public function pullCallback($begin_date, $end_date)
    {
        return $this->pull($this->url['send'],$begin_date, $end_date);
    }

    /**
     * 拉取回复信息
     *
     * @param int $begin_date 开始时间，yyyymmddhh 需要拉取的起始时间,精确到小时
     * @param int $end_date   结束时间，yyyymmddhh 需要拉取的截止时间,精确到小时
     * @return string 应答json字符串，详细内容参见腾讯云协议文档
     */
    public function pullReply($begin_date, $end_date)
    {
        return $this->pull($this->url['reply'],$begin_date, $end_date);
    }
}