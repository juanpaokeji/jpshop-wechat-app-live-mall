<?php
//define your token
//define("TOKEN", "PeMYZGD78grrHN2RGGb2GhyNNb7e2NT8");
//$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
//
//class wechatCallbackapiTest
//{
//    public function valid()
//    {
//        $echoStr = $_GET["echostr"];
//
//        //valid signature , option
//        if($this->checkSignature()){
//            echo $echoStr;
//            exit;
//        }
//    }
//
//    public function responseMsg()
//    {
//        //get post data, May be due to the different environments
//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//
//        //extract post data
//        if (!empty($postStr)){
//            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
//               the best way is to check the validity of xml by yourself */
//            libxml_disable_entity_loader(true);
//            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
//            $fromUsername = $postObj->FromUserName;
//            $toUsername = $postObj->ToUserName;
//            $keyword = trim($postObj->Content);
//            $time = time();
//            $textTpl = "<xml>
//							<ToUserName><![CDATA[%s]]></ToUserName>
//							<FromUserName><![CDATA[%s]]></FromUserName>
//							<CreateTime>%s</CreateTime>
//							<MsgType><![CDATA[%s]]></MsgType>
//							<Content><![CDATA[%s]]></Content>
//							<FuncFlag>0</FuncFlag>
//							</xml>";
//            if(!empty( $keyword ))
//            {
//                $msgType = "text";
//                $contentStr = "Welcome to wechat world!";
//                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//                echo $resultStr;
//            }else{
//                echo "Input something...";
//            }
//
//        }else {
//            echo "";
//            exit;
//        }
//    }
//
//    private function checkSignature()
//    {
//        // you must define TOKEN by yourself
//        if (!defined("TOKEN")) {
//            throw new Exception('TOKEN is not defined!');
//        }
//
//        $signature = $_GET["signature"];
//        $timestamp = $_GET["timestamp"];
//        $nonce = $_GET["nonce"];
//
//        $token = TOKEN;
//        $tmpArr = array($token, $timestamp, $nonce);
//        // use SORT_STRING rule
//        sort($tmpArr, SORT_STRING);
//        $tmpStr = implode( $tmpArr );
//        $tmpStr = sha1( $tmpStr );
//
//        if( $tmpStr == $signature ){
//            return true;
//        }else{
//            return false;
//        }
//    }
//}
//







/**
 * Created by 卷泡
 * author: WJR <272074691@qq.com>
 * Created DateTime: 2019/12/09
 */

namespace app\controllers\wechat\officialAccount;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\web\Controller;
use EasyWeChat\Factory;
use Faker\Provider\Uuid;

class OfficialAccountController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    //公众号配置
    public $config = [
        'app_id' => 'wx52095822757a8bf0',
        'secret' => 'f8714328c618aecd1bbc1f2ea4d25f19',
        'token' => 'PeMYZGD78grrHN2RGGb2GhyNNb7e2NT8',
        'response_type' => 'array'
    ];

    //处理公众号事件、消息
    public function actionIndex() {

        $app = Factory::officialAccount($this->config);
        $app->server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    //关注公众号
                    if ($message['Event'] == "subscribe") {
                        setConfig($message['EventKey'], $message['FromUserName'] ,5); //将openid放入redis缓存
                        return ;
                    }
                    //取消关注公众号
                    if ($message['Event'] == "unsubscribe") {
                        $model = new MerchantModel();
                        $where['wx_open_id'] = $message['FromUserName'];
                        $array = $model->findone($where);
                        if ($array['status'] == 200){
                            $data['id'] = $array['data']['id'];
                            $data['wx_open_id'] = '';
                            $model->update($data);
                        }
                        return ;
                    }
                    //已关注公众号
                    if ($message['Event'] == "SCAN") {
                        setConfig($message['EventKey'], $message['FromUserName'] ,5); //将openid放入redis缓存
                        return ;
                    }
                    //新订单模板消息推送
                    if ($message['Event'] == "TEMPLATESENDJOBFINISH" && $message['Status'] == "success") {
                        $model = new MerchantModel();
                        $where['wx_open_id'] = $message['FromUserName'];
                        $array = $model->findone($where);
                        if ($array['status'] == 200){
                            $data['id'] = $array['data']['id'];
                            $data['template_message_last_time'] = time();
                            $model->update($data);
                        }
                        return ;
                    }
                    return ;
                    break;
                case 'text':
//                    return '收到文本消息';
                    return ;
                    break;
                case 'image':
//                    return '收到图片消息';
                    return ;
                    break;
                case 'voice':
//                    return '收到语音消息';
                    return ;
                    break;
                case 'video':
//                    return '收到视频消息';
                    return ;
                    break;
                case 'location':
//                    return '收到坐标消息';
                    return ;
                    break;
                case 'link':
//                    return '收到链接消息';
                    return ;
                    break;
                case 'file':
//                    return '收到文件消息';
                    return ;
                    break;
                default:
//                    return '收到其它消息';
                    return ;
                    break;
            }
        });

        $app->server->serve()->send();

    }

    //获取公众号临时二维码
    public function actionWxPic() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            if (isset($_COOKIE['wechat_flag'])){
                $weChatFlag = $_COOKIE["wechat_flag"];
            } else {
                //设置cookie
                $weChatFlag = Uuid::uuid();
                setcookie("wechat_flag", $weChatFlag, time()+3600);
            }

            if (isset($_COOKIE['qr_url'])){
                $data['wechat_flag'] = $weChatFlag;
                $data['url'] = $_COOKIE['qr_url'];
                return result('200', '请求成功', $data);
            }

            //获取公众号实例 必须
            $app = Factory::officialAccount($this->config);
            $res = $app->qrcode->temporary($weChatFlag, 3600);
            $url = $app->qrcode->url($res['ticket']);
            setcookie("qr_url", $url, time()+3600);
            $data['wechat_flag'] = $weChatFlag;
            $data['url'] = $url;
            return result('200', '请求成功', $data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    //新订单模板消息
    public function actionTemplateMessage() {
        $redis =  \Yii::$app->redis;
        $paramsLen = $redis->llen('wechat_template_message');
        if ($paramsLen > 0){
            for ($i = 0; $i < $paramsLen;$i++){
                $paramsList[] = json_decode($redis->rpop('wechat_template_message'),true);
            }
            foreach ($paramsList as $k=>$v){
                $params = $v;
                if($params){
                    $appModel = new AppAccessModel();
                    $aWhere['`key`'] = $params['key'];
                    $appInfo = $appModel->find($aWhere);
                    if ($appInfo['status'] != 200){
                        continue;
                    }

                    $model = new MerchantModel();
                    $res = $model->find(['id'=>$appInfo['data']['merchant_id']]);
                    if ($res['status'] != 200 || empty($res['data']['wx_open_id'])){
                        continue;
                    }

                    //间隔一小时以上推送一次
                    if (!empty($res['data']['template_message_last_time']) && $params['pay_time'] < ($res['data']['template_message_last_time'] + 3600 )){
                        continue;
                    }

                    //获取公众号实例 必须
                    $app = Factory::officialAccount($this->config);

                    $app->template_message->send([
                        'touser' => $res['data']['wx_open_id'],
                        'template_id' => '9dHa-G4p7LqwBFrDFrBErEJP6DPKABuk954mJe6W8BE',  //新订单提醒模板ID（公众平台中）
                        'data' => [
                            'first' => '你有一个新的订单',
                            'keyword1' => $appInfo['data']['name'],
                            'keyword2' => '普通订单',
                            'keyword3' => date("Y-m-d H:i:s",$params['pay_time']),
                            'remark' => '请及时处理！',
                        ],
                    ]);


                }
            }
        }


    }



}