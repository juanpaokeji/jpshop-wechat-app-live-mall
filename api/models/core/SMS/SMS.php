<?php

/**
 * This file is part of JWT.
 *
 */

namespace app\models\core\SMS;

use app\models\admin\system\SystemSmsModel;
use app\models\core\aliyun\AliSms;
use app\models\core\TableModel;
use Qcloud\Sms\SmsSingleSender;
use Qcloud\Sms\SmsVoiceVerifyCodeSender;
use Yii;
use yii\db\Exception;
use app\models\core\SMS\SendStatusPuller; //自定义查看数据统计类

/**
 * 腾讯云短信
 *
 * @version   2018年03月29日
 * @author    JYS <272074691@qq.com>
 * @copyright Copyright 2010-2016 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */

class SMS {

    /**
     * 发送短信
     * @throws Exception if the model cannot be found
     * @return array
     * */
    public function getApp() {
        //获取对应的 appid 和 appkey
        //$table = new TableModel();
//        $app = $table->TableSingle('txy', ['id' => 1]);
//        if (empty($app)) {
//            return [
//                'status' => '500',
//                'message' => '1004 查询失败 接口账号不存在',
//            ];
//        }
        return [
            'status' => '200',
            'appid' => "1400071200",
            'appkey' => "b3db056fb6e021a769cb65ee48275919"
        ];
    }

    /**
     * 单发短信
     * @throws Exception if the model cannot be found
     * @param $phone
     * @return array
     * */
    public function sendOne($phone) {
        $data = self::getApp();
        if ($data['status'] != '200') {
            return $data;
        }

        try {
            $code = rand(1000, 9999);
            $sender = new SmsSingleSender($data['appid'], $data['appkey']);
            $result = $sender->send(0, "86", $phone, "【卷泡】您的手机验证码： {$code}，请于5分钟内填写。如非本人操作，请忽略。", "", "");

            $rsp = json_decode($result, true);

            $rsp['code'] = $code;
            $rsp['content'] = "【卷泡】您的手机验证码： {$code}，请于5分钟内填写。如非本人操作，请忽略。";

            if ($rsp['result'] == 0) {
                return result(200, "发送成功", $rsp);
            } else {
                $rsp['errmsg'] = unicodeDecode($rsp['errmsg']);
                return result(500, $rsp['errmsg']);
            }
            //成功示例 {"result":0,"errmsg":"OK","ext":"","sid":"8:ILTeyellu5hzuoNc7Rw20180329","fee":1}
            //失败示例 {"result":1014,"errmsg":"\u6A21\u7248\u672A\u5BA1\u6279\u6216\u5185\u5BB9\u4E0D\u5339\u914D\uFF0C\u9519\u8BEF\u8BE6\u89E3\u89C1:https://cloud.tencent.com/document/product/382/9558#5-1004.E9.94.99.E8.AF.AF.E8.AF.A6.E8.A7.A3","ext":""}
        } catch (\Exception $e) {
            return result(500, "内部错误");
        }
    }

//    /**
//     * 群发短信
//     * @throws \Swoft\Db\Exception\DbException
//     * @param $phone
//     * @return array
//     **/
//    public function sendMulti($phone)
//    {
//        $data = self::getApp();
//        if ($data['status'] != '200') {
//            return $data;
//        }
//        try {
//            $sender = new SmsMultiSender($data['appid'], $data['appkey']);
//            $result = $sender->send(0, "86", $phone,
//                "【卷泡】您的找回密码手机验证码： 345678，请于10分钟内填写。如非本人操作，请忽略。", "", "");
////            $rsp = json_decode($result);
//            //成功示例 {"result":0,"errmsg":"OK","ext":"","detail":[{"result":0,"errmsg":"OK","mobile":"15366669450","nationcode":"86","sid":"8:yoIn07HUWbhiPfrmTOF20180329","fee":1},{"result":0,"errmsg":"OK","mobile":"15195729049","nationcode":"86","sid":"8:2brk80HHWoJ89Vgo9fJ20180329","fee":1}]}
//            //失败示例 {"result":1016,"errmsg":"\u624B\u673A\u53F7\u683C\u5F0F\u9519\u8BEF","ext":""}
//            var_dump($result);
//        } catch(\Exception $e) {
////            echo var_dump($e);
//        }
//    }

    /**
     * 指定模板ID单发短信
     * @throws Exception if the model cannot be found
     * @param $phone
     * @param $templateId
     * @param $params
     * @param $smsSign
     * @return array
     * */
    public function sendSpecifiedTemplateOne($phone, $templateId, $params, $smsSign) {
        $data = self::getApp();
        if ($data['status'] != '200') {
            return $data;
        }
        try {
            $sender = new SmsSingleSender($data['appid'], $data['appkey']);
            $result = $sender->sendWithParam("86", $phone, $templateId, $params, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
//            $rsp = json_decode($result);
            //成功示例 {"result":0,"errmsg":"OK","ext":"","sid":"8:1s3SchrQpE2Iy91J45b20180329","fee":1}
            //失败示例 {"result":1024,"errmsg":"\u624B\u673A\u53F71\u5C0F\u65F6\u9891\u7387\u9650\u5236","ext":""}
            //  var_dump($result);
            $rsp = json_decode($result, true);
            if ($rsp['result'] == 0) {
                return result(200, "发送成功", $rsp);
            } else {
                return result(500, "发送失败");
            }
        } catch (\Exception $e) {
            return result(500, "内部错误");
        }
    }

    /**
     * 单发短信
     * @throws Exception if the model cannot be found
     * @param $phone
     * @return array
     * */
    public function sendVoiceOne($phone) {
        $data = self::getApp();
        if ($data['status'] != '200') {
            return $data;
        }
        try {
            $sender = new SmsVoiceVerifyCodeSender($data['appid'], $data['appkey']);
            $result = $sender->send("86", $phone, "1024", 2, "");
//            $rsp = json_decode($result);
            //成功示例 {"result":0,"errmsg":"OK","ext":"","callid":"d9e086f6-3337-11e8-906f-5254004815a7"}
            //失败示例 {"result":1014,"errmsg":"template not match.","ext":""}
            $rsp = json_decode($result, true);
            if ($rsp['result'] == 0) {
                return result(200, "发送成功", $rsp);
            } else {
                return result(500, "发送失败");
            }
        } catch (\Exception $e) {
            return result(500, "内部错误");
        }
    }

    /**
     * 发送数据统计
     * @throws Exception if the model cannot be found
     * @param $begin_date
     * @param $end_date
     * @return array
     * */
    public function pullCallbackStatus($begin_date, $end_date) {
        $data = self::getApp();
        if ($data['status'] != '200') {
            return $data;
        }
        try {
            $sender = new SendStatusPuller($data['appid'], $data['appkey']);
            $result = $sender->pullCallback($begin_date, $end_date);  // int $max 拉取最大条数，最多100
//            $rsp = json_decode($result);
            //成功示例 {"result":0,"msg":"OK","data":{"request":30,"success":9,"bill_number":7}}
            //失败示例 {"result":60008,"errmsg":"service timeout or request format error,please check and try again"}
            $rsp = json_decode($result, true);
            if ($rsp['result'] == 0) {
                return result(200, "发送成功", $rsp);
            } else {
                return result(500, "发送失败");
            }
        } catch (\Exception $e) {
            return result(500, "内部错误");
        }
    }

    /**
     * 回执数据统计
     * @throws Exception if the model cannot be found
     * @param $begin_date
     * @param $end_date
     * @return array
     * */
    public function pullReplyStatus($begin_date, $end_date) {
        $data = self::getApp();
        if ($data['status'] != '200') {
            return $data;
        }
        try {
            $sender = new SendStatusPuller($data['appid'], $data['appkey']);
            $result = $sender->pullReply($begin_date, $end_date);  // int $max 拉取最大条数，最多100
//            $rsp = json_decode($result);
            //成功示例 {"result":0,"errmsg":"OK","ext":"","sid":"8:1s3SchrQpE2Iy91J45b20180329","fee":1}
            //失败示例 {"result":60008,"errmsg":"service timeout or request format error,please check and try again"}
            $rsp = json_decode($result, true);
            if ($rsp['result'] == 0) {
                return result(200, "发送成功", $rsp);
            } else {
                return result(500, "发送失败");
            }
        } catch (\Exception $e) {
            return result(500, "内部错误");
        }
    }

    /**
     * 指定模板单发（判断短信渠道）
     *
     * @param string $phone       手机号
     * @param int    $templId     模板 id
     * @param array  $data        模板参数
     * @param string $sign        签名
     * @return string 应答json字符串
     */
    public function sendSms($phone,$templId,$data = []){
        $smsModel = new SystemSmsModel();
        $smsWhere['status'] = 1;
        $smsInfo = $smsModel->do_one($smsWhere); //查询配置
        if ($smsInfo['status'] != 200){
            return result(500, "未查询到配置");
        }
        $smsInfo['data']['config'] = json_decode($smsInfo['data']['config'], true);

        //判断短信渠道
        if ($smsInfo['data']['type'] == 1){ //腾讯云
            $sender = new SmsSingleSender($smsInfo['data']['config']['appid'], $smsInfo['data']['config']['appkey']);
            $sendResult = $sender->sendWithParam("86", $phone, $templId, $data, $smsInfo['data']['config']['sign']);
            $sendRes = json_decode($sendResult, true);
            if (isset($sendRes['result']) && $sendRes['result'] == 0) {
                return result(200, "发送成功");
            } else {
                $sms_error['result'] = $sendRes['result'];
                $sms_error['errmsg'] = unicodeDecode($sendRes['errmsg']);
                file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . json_encode($sms_error, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
                return result(500, "发送失败");
            }
        }elseif ($smsInfo['data']['type'] == 2){ //阿里云
            $sender = new AliSms();
            $sendRes = $sender->sendSms($phone,$smsInfo['data']['config']['sign'],$templId,$data);
            return $sendRes;
        }else{
            return result(500, "短信配置有误");
        }
    }

}
