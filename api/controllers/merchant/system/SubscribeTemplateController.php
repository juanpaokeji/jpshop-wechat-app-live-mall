<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\shop\OrderModel;
use app\models\shop\ShopSubscribeMessageNumModel;
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

            if ($subscribeInfo['status'] == 200){
                foreach ($subscribeInfo['data'] as $k=>$v){
                    $templatePurpose[] = $v['template_purpose'];
                }
            }

            //添加小程序模板（模板需要变动，修改data参数）
            $url = "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token={$token['access_token']}";
            if ($subscribeInfo['status'] == 204 || !in_array('send_goods',$templatePurpose)){
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
            }
            if ($subscribeInfo['status'] == 204 || !in_array('check',$templatePurpose)){
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
            }
            if ($subscribeInfo['status'] == 204 || !in_array('assemble',$templatePurpose)){
                $data = json_encode(['tid'=>'4213','kidList'=>[2,3,4,5],'sceneDesc'=>'拼团进度通知']);
                $res = json_decode(curlPostJson($url,$data),true);  //拼团进度通知模板
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
            }
            if ($subscribeInfo['status'] == 204 || !in_array('merchandise_arrival',$templatePurpose)){
                $data = json_encode(['tid'=>'279','kidList'=>[1,3,4,5],'sceneDesc'=>'预约到货通知']);
                $res = json_decode(curlPostJson($url,$data),true);  //预约到货通知模板
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
            }
            if ($subscribeInfo['status'] == 204 || !in_array('presale',$templatePurpose)){
                $data = json_encode(['tid'=>'4487','kidList'=>[1,2,3,4],'sceneDesc'=>'预售尾款支付提醒']);
                $res = json_decode(curlPostJson($url,$data),true);  //预售尾款支付提醒模板
                if ($res['errcode'] == 0){
                    $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                    $subscribeData = array(
                        'key'=>$params['key'],
                        'merchant_id'=>yii::$app->session['uid'],
                        'template_id'=>$res['priTmplId'],
                        'template_purpose'=>'presale',
                    );
                    $subscribeModel->do_add($subscribeData);
                }
            }
            if ($subscribeInfo['status'] == 204 || !in_array('bargain',$templatePurpose)){
                $data = json_encode(['tid'=>'2920','kidList'=>[1,2,5,6],'sceneDesc'=>'砍价结果通知']);
                $res = json_decode(curlPostJson($url,$data),true);  //砍价结果通知
                if ($res['errcode'] == 0){
                    $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                    $subscribeData = array(
                        'key'=>$params['key'],
                        'merchant_id'=>yii::$app->session['uid'],
                        'template_id'=>$res['priTmplId'],
                        'template_purpose'=>'bargain',
                    );
                    $subscribeModel->do_add($subscribeData);
                }
            }
            if ($subscribeInfo['status'] == 204 || !in_array('pick_up_notice',$templatePurpose)){
                $data = json_encode(['tid'=>'1193','kidList'=>[6,10,11,12,2],'sceneDesc'=>'取货通知']);
                $res = json_decode(curlPostJson($url,$data),true);  //取货通知
                if ($res['errcode'] == 0){
                    $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                    $subscribeData = array(
                        'key'=>$params['key'],
                        'merchant_id'=>yii::$app->session['uid'],
                        'template_id'=>$res['priTmplId'],
                        'template_purpose'=>'pick_up_notice',
                    );
                    $subscribeModel->do_add($subscribeData);
                }
            }
            if ($subscribeInfo['status'] == 204 || !in_array('refund',$templatePurpose)){
                $data = json_encode(['tid'=>'642','kidList'=>[1,2,3,7,15],'sceneDesc'=>'退款提醒']);
                $res = json_decode(curlPostJson($url,$data),true);  //退款提醒
                if ($res['errcode'] == 0){
                    $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
                    $subscribeData = array(
                        'key'=>$params['key'],
                        'merchant_id'=>yii::$app->session['uid'],
                        'template_id'=>$res['priTmplId'],
                        'template_purpose'=>'refund',
                    );
                    $subscribeModel->do_add($subscribeData);
                }
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

    public function actionUpdate($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['template_id'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemMerchantMiniSubscribeTemplateModel();
            $where['id'] = $id;
            $data['template_id'] = $params['template_id'];
            $array = $model->do_update($where,$data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
}
