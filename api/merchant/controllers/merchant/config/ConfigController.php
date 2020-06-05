<?php

namespace app\controllers\merchant\config;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\system\SystemWxConfigModel;
use app\models\core\Base64Model;
use EasyWeChat\Factory;
use app\models\merchant\app\AppAccessModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class ConfigController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                //    'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['openapp'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionSingle()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemWxConfigModel();
            $must = ['key', 'config_type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $params = $request->get(); //获取body传参
            $data['`key`'] = $params['key'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $array = $model->find($params);
            if ($array['status'] != 200) {
                return result(500, "找不到该配置文件！");
            }
            if ($params['config_type'] == "wechat") {
                $data = json_decode($array['data']['wechat_info'], true);
                $wechat = json_decode($array['data']['wechat'], true);
                if ($wechat['type'] != 2) {
                    $docking = json_decode($array['data']['wechat'], true);
                    $data['url'] = $docking['url'];
                    $data['token'] = $docking['token'];
                    $data['aes_key'] = $docking['aes_key'];
                }
                return result(200, "请求成功！", $data);
            } else if ($params['config_type'] == "wxpay") {
                $data = json_decode($array['data']['wechat_pay'], true);
                if ($data != "") {
                    if (isset($data['key'])) {
                        $data['pay_key'] = $data['key'];
                    }
                }
                return result(200, "请求成功！", $data);
            } else if ($params['config_type'] == "miniprogram") {
                $data = json_decode($array['data']['miniprogram'], true);
                $arr['app_id'] = $data['app_id'];
                $arr['nick_name'] = $data['nick_name'];
                $arr['head_img'] = $data['head_img'];
                return result(200, "请求成功！", $arr);
            } else if ($params['config_type'] == "miniprogrampay") {
                $data = json_decode($array['data']['miniprogram_pay'], true);
                $data['pay_key'] = $data['key'];
                return result(200, "请求成功！", $data);
            } else {
                return result(500, "请求失败！");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemWxConfigModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $params = $request->get(); //获取body传参
;
            $systemConfigModel = new SystemWxConfigModel();
            $system['key'] =$params['key'];
            $systemConfig = $systemConfigModel->find($system);
            if($systemConfig['status']!=200){
                return $systemConfig;
            }
            $a = json_decode($systemConfig['data']['miniprogram'],true);
            $systemConfig['data']['app_id'] =$a['app_id'];
            $systemConfig['data']['secret'] =$a['secret'];
            $systemConfig['data']['saobei']= json_decode($systemConfig['data']['saobei'],true);
            $systemConfig['data']['wx'] = json_decode($systemConfig['data']['miniprogram_pay'],true);
            $systemConfig['data']['wx']['pay_key'] = $systemConfig['data']['wx']['key'];
            return $systemConfig;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new SystemWxConfigModel();
            $base = new Base64Model();
            $must = ['key', 'wx_pay_type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $systemConfigModel = new SystemWxConfigModel();
            $system['key'] ='ccvWPn';
            $systemConfig = $systemConfigModel->find($system);
            if ($systemConfig['status'] != 200) {
                return result(500, "找不到该配置文件！");
            }
            //小程序支付
            $data['miniprogram_pay']['key'] = $params['wx']['pay_key'];
            $data['miniprogram_pay']['app_id'] = $params['app_id'];
            $data['miniprogram_pay']['secret'] = $params['secret'];
            $data['miniprogram_pay']['mch_id'] = $params['wx']['mch_id'];
            $miniprogram_pay = json_decode($systemConfig['data']['miniprogram_pay'],true);
            if ($params['wx']['cert_path'] != "") {
                $data['miniprogram_pay']['cert_path']= $base->base64_file_content($params['wx']['cert_path'],"uploads/pem/" . yii::$app->session['uid']);
            }else{
                $data['miniprogram_pay']['cert_path'] = $miniprogram_pay['cert_path'];
            }
            if ($params['wx']['key_path'] != "") {
                $data['miniprogram_pay']['key_path'] = $base->base64_file_content($params['wx']['key_path'], "uploads/pem/" . yii::$app->session['uid']);
            }else{
                $data['miniprogram_pay']['key_path'] = $miniprogram_pay['key_path'];
            }
            $data['miniprogram_pay'] ['wx_pay_type']= $params['wx_pay_type'];
            $data['miniprogram_pay']=json_encode($data['miniprogram_pay']);
            //小程序基础信息
            $data['miniprogram'] =json_encode(['app_id'=>$params['app_id'],'secret'=>$params['secret']]);
            //扫呗
            $params['saobei']['app_id']=$params['app_id'];
            $data['saobei']=json_encode($params['saobei']);



            $data['miniprogram_id'] = $params['app_id'];
            $data['key']= 'ccvWPn';
            $data['id'] = 331;
            $data['merchant_id']=13;
            $data['wx_pay_type'] = $params['wx_pay_type'];
            $rs = $model->update($data);

            $systemConfigModel = new SystemWxConfigModel();
            $system['key'] = $data['key'];
            $systemConfig = $systemConfigModel->find($system);
            $array['miniprogram'] = $systemConfig['data']['miniprogram'];
            $array['miniprogrampay'] = $systemConfig['data']['miniprogram_pay'];
            $array['wx_pay_type'] = $systemConfig['data']['wx_pay_type'];
            setConfig($data['key'], $array);
            return $rs;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new AppModel();
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $app->delete($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionOpenapp() {
//        $str = '{"type":1,"url":"https://api2.juanpao.com/wx?key=FPpGzY","app_id":"wx77ed974c23c54a7d","wechat_id":"gh_da8d160192f3","token":"CbM8nGBomavHYlKeaHaimzwKgKxE3h9Q","aes_key":"rRqDfOAWihkxylO01Jn47HlCV7JUiCUyiwbmv0Q7ozR","secret":"64b50927543d3122a845a8639983696a"}';
//        $config = json_decode($str, true);
//        $app = Factory::officialAccount($config);
//        $token = $app->access_token->getToken();
//       
//        $config = [
//            'app_id' => 'wx8df3a6f4a4f9ec54',
//            'secret' => '7188287cd30aa902d5933654fed60559',
//            'token' => 'juanPao',
//            'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA'
//        ];
//        // $config = json_decode($str, true);
//
//        $openPlatform = Factory::openPlatform($config);
//        $officialAccount = $openPlatform->officialAccount("wx77ed974c23c54a7d", $token["access_token"]);
//        $account = $officialAccount->account;
//        $result = $account->getBinding();
//        var_dump($result);
//        die();
//    }

    public function actionConfig()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $app = new AppAccessModel();
            $data['`key`'] = $params['key'];
            $data['merchant_id'] = yii::$app->session['uid'];

            $config = $app->find($data);
            if ($config['status'] == 200) {
                $config['data'] = json_decode($config['data']['config'], true);
            }
            return $config;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionConfigup()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new AppAccessModel();

            $data['`key`'] = $params['key'];
            unset($params['key']);
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['config'] = json_encode($params);
            $config = $app->update($data);
            if ($config['status'] == 200) {
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $data['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $data['merchant_id'];
                $operationRecordData['module_name'] = '优惠券配置';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $config;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionMinione()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参

            $model = new SystemWxConfigModel();
            $rs = $model->find(['id' => 331, 'key' => 'ccvWPn', 'merchant_id' => 13]);

            if ($rs['status'] == 200) {
                return result(200, '请求成功', json_decode($rs['data']['miniprogram']));
            } else {
                return $rs;
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionMini()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemWxConfigModel();
            $key = $params['key'];
            unset($params['key']);
            $a = json_encode($params);
            $rs = $model->update(['id' => 331, 'key' => 'ccvWPn', 'merchant_id' => '13', 'miniprogram' => $a, 'miniprogram_id' => $params['app_id']]);
            $systemConfigModel = new SystemWxConfigModel();
            $system['key'] = $key;
            $systemConfig = $systemConfigModel->find($system);
            $array['miniprogram'] = $systemConfig['data']['miniprogram'];
            $array['miniprogram_pay'] = $systemConfig['data']['miniprogram_pay'];
            $array['wx_pay_type'] = $systemConfig['data']['wx_pay_type'];
            setConfig($key, $array);
            return $rs;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
