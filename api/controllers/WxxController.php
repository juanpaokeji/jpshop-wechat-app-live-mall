<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers;

use DeepCopy\f002\A;
use yii;
use yii\web\Controller;
use EasyWeChat\Factory;
use app\models\system\SystemAutoWordsModel;
use yii\web\MerchantController;
use app\php\wxBizMsgCrypt;
use app\php\Prpcrypt;
use yii\redis\Cache;
use tools\wechat\Authorization;

include_once "php/wxBizMsgCrypt.php";
include_once "php/pkcs7Encoder.php";
include_once "../extend/tools/wechat/Authorization.php";

define('TOKEN', 'juanPao');

class WxxController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置 必须设置


    //获取 获取微信第三方component_access_token
    public function actionGetcat()
    {
        $authorization = new Authorization();
//        //获取 component_access_token 完成
//        var_dump(($authorization->get_component_access_token()));

//        //获取 pre_auth_code 完成
//        var_dump(($authorization->get_pre_auth_code()));

        //获取预授权码 完成
        $request = request();
        $data = $authorization->api_query_auth($request['params']['auth_code']);
        $authorizer_appid = $data['authorization_info']['authorizer_appid'];

//        //获取第三方令牌 完成
//        $query_auth = $authorization->get_authorizer_access_token($authorizer_appid);

        //获取授权方公众号信息
        $oa_Info =  $authorization->get_authorizer_info($authorizer_appid);
        return result('200', '成功', $oa_Info);
    }

    public function actionIndex()
    {
        //第三方授权获取微信10分钟一次推送信息，获取 ComponentVerifyTicket
        $appId = 'wx8df3a6f4a4f9ec54';//第三方平台 appID
        $token = 'juanPao';
        $encodingAesKey = '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA';
        $encryptMsg = file_get_contents("php://input");

        $xml_tree = new \DOMDocument();
        $xml_tree->loadXML($encryptMsg);
        $array_e = $xml_tree->getElementsByTagName('Encrypt');
        $encrypt = $array_e->item(0)->nodeValue;

        $pc = new wxBizMsgCrypt($token, $encodingAesKey, $appId);

        $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
        $from_xml = sprintf($format, $encrypt);
        $msg = '';
        $errCode = $pc->decryptMsg($_GET ['msg_signature'], $_GET ['timestamp'], $_GET ['nonce'], $from_xml, $msg);
        if ($errCode == 0) {
            //正确 从xml中拿到 ComponentVerifyTicket
            $xml_tree->loadXML($msg);
            $ticket = $xml_tree->getElementsByTagName('ComponentVerifyTicket');
            $ComponentVerifyTicket = $ticket->item(0)->nodeValue;

            $Cache = new Cache();
            $cacheData = [
                'app_id' => $appId,
                'component_verify_ticket' => $ComponentVerifyTicket,
            ];
            $Cache->set('ComponentVerifyTicket',$cacheData);
        } else {
            //错误
            $this->logger($errCode);
        }

//        $this->responseMsg();
//        $this->broadCasting();
    }

    /**
     * 消息及事件入口文件
     * @return array|string|bool
     * @throws
     */
    public function responseMsg()
    {
        $app = Factory::officialAccount($this->getWxConfig());
        $app->server->push(function ($message) {

            return $message;
//            return '<a href="www.baidu.com">跳转链接</a>';
            //       return '<img src="http://juanpao999-1255754174.cos.cn-south.myqcloud.com/goods%2F2018%2F11%2F19%2F15425900705bf20e763fc8f.jpg" />';
//            switch ($message['MsgType']) {
//                case 'event':
////                    if ($message['Event'] == "subscribe") {
////                        $data['auto_type'] = 1;
////                        $data['merchant_id'] = 40;
////                        $rs = $wordsModel->find($data);
////                        return var_dump($rs);
////                    } else {
////                        return '收到事件消息';
////                    }
//                    return '收到事件消息';
//                    break;
//                case 'text':
//                    return '收到文字消息';
//                    break;
//                case 'image':
//                    return '收到图片消息';
//                    break;
//                case 'voice':
//                    return '收到语音消息';
//                    break;
//                case 'video':
//                    return '收到视频消息';
//                    break;
//                case 'location':
//                    return '收到坐标消息';
//                    break;
//                case 'link':
//                    return '收到链接消息';
//                    break;
//                case 'file':
//                    return '收到文件消息';
//                default:
//                    return '收到其它消息';
//                    break;
//            }
        });
        $app->server->serve()->send();
    }

    /**
     * @param $log_content
     */
    private function logger($log_content)
    {
        if (isset($_SERVER['HTTP_APPNAME'])) {   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        } else if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1") { //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if (file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)) {
                unlink($log_filename);
            }
            file_put_contents($log_filename, date('Y-m-d H:i:s') . " " . $log_content . "\r\n", FILE_APPEND);
        }
    }

}
