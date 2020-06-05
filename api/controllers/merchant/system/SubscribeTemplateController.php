<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\shop\OrderModel;
use app\models\shop\UserModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateModel;
use yii;
use yii\web\MerchantController;
use EasyWeChat\Factory;
use app\models\system\SystemMiniTemplateModel;
use app\models\system\SystemMerchantMiniTemplateModel;

class SubscribeTemplateController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
            $subscribeWhere['key'] = $params['key'];
            $subscribeWhere['merchant_id'] = yii::$app->session['uid'];
            $subscribeWhere['limit'] = false;
            $array = $subscribeModel->do_select($subscribeWhere);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            //获取小程序
            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "小程序信息错误");
            }

            try{
                $miniProgram = Factory::miniProgram($config);
                $token = $miniProgram->access_token->getToken(true);// 强制重新从微信服务器获取 token
            }catch (\EasyWeChat\Kernel\Exceptions\Exception $exception){
                return result(500, "小程序信息错误");
            }
            if (!isset($token['access_token'])){
                return result(500, "小程序信息错误");
            }
            $miniProgram['access_token']->setToken($token['access_token'], 3600);
            //删除本地和小程序模板
            $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
            $subscribeWhere['key'] = $params['key'];
            $subscribeWhere['merchant_id'] = yii::$app->session['uid'];
            $subscribeWhere['limit'] = false;
            $subscribeInfo = $subscribeModel->do_select($subscribeWhere);
            $url = "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token={$token['access_token']}";
            if ($subscribeInfo['status'] == 200){
                foreach ($subscribeInfo['data'] as $k=>$v){
                    $delData['priTmplId'] = $v['template_id'];
                    curlPostJson($url,json_encode($delData));
                    $subscribeModel->do_del(['merchant_id' => yii::$app->session['uid'], 'key' => $params['key']]);
                }
            }
            //添加小程序模板（模板需要变动，修改data参数）
            $url = "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token={$token['access_token']}";
            $data = json_encode(['tid'=>'6220','kidList'=>[1,2,8,9],'sceneDesc'=>'您的订单已发货,请注意查收']);
            $res = json_decode(curlPostJson($url,$data),true);  //订单发货模板
            if ($res['errcode'] == 0){
                $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                $subscribeData = array(
                    'key'=>$params['key'],
                    'merchant_id'=>yii::$app->session['uid'],
                    'template_id'=>$res['priTmplId'],
                    'template_purpose'=>'send_goods',
                );
                $subscribeModel->do_add($subscribeData);
            }
            $data = json_encode(['tid'=>'1482','kidList'=>[1,2,3,8,5],'sceneDesc'=>'已审核']);
            $res = json_decode(curlPostJson($url,$data),true);  //申请审核模板
            if ($res['errcode'] == 0){
                $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                $subscribeData = array(
                    'key'=>$params['key'],
                    'merchant_id'=>yii::$app->session['uid'],
                    'template_id'=>$res['priTmplId'],
                    'template_purpose'=>'check',
                );
                $subscribeModel->do_add($subscribeData);
            }
            $data = json_encode(['tid'=>'2117','kidList'=>[1,4,5,7],'sceneDesc'=>'拼团成功']);
            $res = json_decode(curlPostJson($url,$data),true);  //拼团成功模板
            if ($res['errcode'] == 0){
                $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                $subscribeData = array(
                    'key'=>$params['key'],
                    'merchant_id'=>yii::$app->session['uid'],
                    'template_id'=>$res['priTmplId'],
                    'template_purpose'=>'assemble',
                );
                $subscribeModel->do_add($subscribeData);
            }
            $data = json_encode(['tid'=>'2269','kidList'=>[1,2],'sceneDesc'=>'预约商品到货通知']);
            $res = json_decode(curlPostJson($url,$data),true);  //预约商品到货通知模板
            if ($res['errcode'] == 0){
                $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                $subscribeData = array(
                    'key'=>$params['key'],
                    'merchant_id'=>yii::$app->session['uid'],
                    'template_id'=>$res['priTmplId'],
                    'template_purpose'=>'merchandise_arrival',
                );
                $subscribeModel->do_add($subscribeData);
            }

            //获取小程序模板添加到数据库
            $url = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token={$token['access_token']}";
            $template = json_decode(curlGet($url),true);
            $merchantTemp = $subscribeModel->do_select($subscribeWhere);
            if ($merchantTemp['status'] != 200){
                return result(500, "请求失败,小程序未添加相应类目");
            }
            if ($template['errcode'] == 0 && $merchantTemp['status'] == 200){
                foreach ($template['data'] as $k=>$v){
                    foreach ($merchantTemp['data'] as $key=>$val){
                        if ($val['template_id'] == $v['priTmplId']){
                            $tempWhere['id'] = $val['id'];
                            $tempData = array(
                                'name'=>$v['title'],
                                'content'=>$v['content'],
                                'example'=>$v['example'],
                                'type'=>$v['type'],
                            );
                            $subscribeModel->do_update($tempWhere,$tempData);
                        }
                    }
                }
                return result(200, "请求成功");
            }else{
                return result(500, "请求失败");
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

}
