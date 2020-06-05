<?php

namespace app\controllers\message;


use app\models\system\SystemMerchantMiniSubscribeTemplateAccessModel;
use EasyWeChat\Factory;
use yii;
use yii\db\Exception;
use yii\web\Controller;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class SubscribeMessageController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionIndex()
    {

        $model = new SystemMerchantMiniSubscribeTemplateAccessModel();
        $message = $model->do_select(['status' => -1, 'limit' => 30]);
        if ($message['status'] != 200){
            return $message;
        }
        //获取小程序
        $config = $this->getSystemConfig($message['data'][0]['key'], "miniprogram");
        if ($config == false) {
            return result(500, "小程序信息错误");
        }

        try{
            $miniProgram = Factory::miniProgram($config);
//            $token = $miniProgram->access_token->getToken();
            $token = $miniProgram->access_token->getToken(true);// 强制重新从微信服务器获取 token
        }catch (\EasyWeChat\Kernel\Exceptions\Exception $exception){
            return result(500, "小程序信息错误");
        }
        if (!isset($token['access_token'])){
            return result(500, "小程序信息错误");
        }
        $miniProgram['access_token']->setToken($token['access_token'], 3600);

        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$token['access_token']}";
        foreach ($message['data'] as $key=>$val){
            $data = array(
                'touser'=>$val['mini_open_id'],   //openid
                'template_id'=>$val['template_id'],  //模板ID
                'page'=>$val['page'], //跳转地址
                'data'=>json_decode($val['template_params'],true), //消息内容
            );
            $res = json_decode(curlPostJson($url,json_encode($data)),true);
            if ($res['errcode'] == 0){
                $accessData['number'] = $val['number'] + 1;
                $accessData['status'] = 1;
            }else{
                $accessData['number'] = $val['number'] + 1;
                $accessData['status'] = 2;
                file_put_contents(Yii::getAlias('@webroot/') . '/SubscribeMessage.text', date('Y-m-d H:i:s') . json_encode($res,JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
            }
            $model->do_update(['id'=>$val['id']],$accessData);
        }
        return;
    }

}
