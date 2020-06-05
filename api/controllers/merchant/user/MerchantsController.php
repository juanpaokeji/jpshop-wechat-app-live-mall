<?php

namespace app\controllers\merchant\user;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\user\MerchantModel;
use app\models\core\Base64Model;
use app\models\admin\user\SystemAccessModel;

/**
 * 商户接口控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class MerchantsController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数
            $merchant = new MerchantModel();
            $array = $merchant->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $merchant = new MerchantModel();
            $params['id'] = yii::$app->session['uid'];
            $array = $merchant->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $merchant = new MerchantModel();
        $must = ['name', 'real_name', 'password', 'phone'];
        $params['app_key'] = $this->get_randomstr(32);
        $params['app_secret'] = $this->get_randomstr(32);
        $rs = $this->checkInput($must, $params);
        if ($rs != false) {
            return $rs;
        }
        $config = array();
        $config['weixin_pay'] = array(
            "APPID" => "",
            "MCHID" => "",
            "KEY" => "",
            "APPSECRET" => "",
            "SSLCERT_PATH" => "",
            "SSLKEY_PATH" => "",
            "REPORT_LEVENL" => "",
            "CURL_PROXY_HOST" => "0.0.0.0",
            "CURL_PROXY_PORT" => 0,
        );
        $config['ali_pay'] = array(
            //应用ID,您的APPID。
            'app_id' => "",
            //商户私钥
            'merchant_private_key' => "",
            //异步通知地址
            'notify_url' => "http://www.baidu.com",
            //同步跳转
            'return_url' => "http://www.baidu.com",
            //编码格式
            'charset' => "",
            //签名方式
            'sign_type' => "",
            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
            //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
            'alipay_public_key' => "",
        );
        $params['config'] = json_encode($config);
        $params['salt'] = $this->get_randomstr(32);
        $data = [
            'name' => $params['name'],
            'real_name' => $params['real_name'],
            'password' => md5($params['password'] . $params['salt']),
            'salt' => $params['salt'],
            'phone' => $params['phone'],
            'app_key' => $params['app_key'],
            'app_secret' => $params['app_secret'],
            'config' => $params['config'],
            'status' => $params['status'],
            'create_time' => time(),
        ];

        $access = new SystemAccessModel();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $array = $merchant->add($data);
            $data1 = array(
                'uid' => $array['data'],
                'type' => 2,
                'group_ids' => $params['group_id'],
                'create_time' => time(),
            );
            $isok = $access->add($data1);
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            return result(200, "添加成功", $array['data']);
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "添加失败");
        }
    }

    public function actionWx($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request;
            $params = $request->get(); //获取地址栏参数
            $params['id'] = $id;
            $merchant = new MerchantModel();
            $array = $merchant->findWeixin($params);
            return $array;
        }
    }

    public function actionAli($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        $merchant = new MerchantModel();
        $params['id'] = $id;
        $array = $merchant->findAli($params);
        return $array;
    }

    public function actionUpdate() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $merchant = new MerchantModel();
        $params['id'] = yii::$app->session['uid'];
        if (!isset($params['id'])) {
            $array = json_encode(['status' => 400, 'message' => '缺少参数 id'], JSON_UNESCAPED_UNICODE);
        } else {
            if (isset($params['password'])) {
                if ($params['password'] == "") {
                    unset($params['password']);
                } else {
                    $res = $merchant->find($params);
                    if($res['status']!=200){
                        return $res;
                    }
                    if($res['data']['password']!=md5($params['old'].$res['data']['salt'])){
                        return result(500,'原密码不正确');
                    }
                    $salt = $this->get_randomstr(32);
                    $params['password'] = md5($params['password'] . $salt);
                    $params['salt'] = $salt;
                    unset($params['old']);
                    unset($params['confirm_new']);
                    $array = $merchant->update($params);
                }
            }else if (isset($params['group_id'])) {
                $access = new SystemAccessModel();
                $transaction = yii::$app->db->beginTransaction();
                try {
                    $data = array(
                        'uid' => $params['id'],
                        'type' => 2,
                        'group_ids' => $params['group_id'],
                        'update_time' => time(),
                    );
                    unset($params['group_id']);
                    $array = $merchant->update($params);

                    $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                    return result(200, "修改成功");
                } catch (Exception $e) {
                    $transaction->rollBack(); //回滚
                    return result(500, "修改失败");
                }
            } else {
                $array = $merchant->update($params);
            }
        }
        return $array;
    }

    public function actionUpdatewx($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取地址栏参数
        $merchant = new MerchantModel();
        $params['id'] = $id;
        if (!isset($params['id'])) {
            $array = json_encode(['status' => 400, 'message' => '缺少参数 id'], JSON_UNESCAPED_UNICODE);
        } else {
            $list = $merchant->find($params);
            //判断是否存在微信配置文件参数
            if (isset($params['APPID']) && isset($params['MCHID']) && isset($params['KEY']) && isset($params['APPSECRET']) && isset($params['SSLCERT_PATH']) && isset($params['SSLKEY_PATH'])) {
                //取出微信配置参数 OBJECT 转array
                $config = json_decode($list['data']['config'], true);

                $config['weixin_pay'] = $config['weixin_pay'];
                //循环判断参数赋值
                foreach ($params as $key => $value) {
                    if ($value != "") {
                        $config['weixin_pay'][$key] = $value;
                    }
                }
                //文件base64 转本地存储
                if ($params['SSLCERT_PATH'] != "") {
                    $base = new Base64Model();
                    $config['weixin_pay']['SSLCERT_PATH'] = $base->base64_file_content($params['SSLCERT_PATH'], "uploads/pem/" . $params['id']);
                }
                if ($params['SSLKEY_PATH'] != "") {
                    if ($params['SSLKEY_PATH'] != "") {
                        $base = new Base64Model();
                        $config['weixin_pay']['SSLCERT_PATH'] = $base->base64_file_content($params['SSLKEY_PATH'], "uploads/pem/" . $params['id']);
                    }
                }
            } else {

                return ['status' => 400, 'message' => '参数不全'];
            }
            unset($config['weixin_pay']['id']);
            $data['config'] = json_encode($config);

            $data['id'] = $id;
            $array = $merchant->update($data);
            //      $array = json_encode(['status' => 400, 'message' => '12312'], JSON_UNESCAPED_UNICODE);
        }
        return $array;
    }

    public function actionUpdateali($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取地址栏参数
        $merchant = new MerchantModel();
        $params['id'] = $id;
        if (!isset($params['id'])) {
            $array = json_encode(['status' => 400, 'message' => '缺少参数 id'], JSON_UNESCAPED_UNICODE);
        } else {
            $list = $merchant->find($params);
            if (isset($params['app_id']) && isset($params['merchant_private_key']) && isset($params['charset']) && isset($params['sign_type']) && isset($params['alipay_public_key'])) {
                //取出支付宝配置参数 OBJECT 转array
                $config = json_decode($list['data']['config'], true);

                $config['ali_pay'] = $config['ali_pay'];
                //循环判断参数赋值
                foreach ($params as $key => $value) {
                    if ($value != "") {
                        $config['ali_pay'][$key] = $value;
                    }
                }
            } else {

                return ['status' => 400, 'message' => '参数不全'];
            }
            unset($config['ali_pay']['id']);
            $data['config'] = json_encode($config);
            $data['id'] = $params['id'];
            $array = $merchant->update($data);
        }
        return $array;
    }

    public function actionDelete($id) {

        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        $merchant = new MerchantModel();
        $params['id'] = $id;
        if (!isset($params['id'])) {
            return result(400, '缺少参数 id');
        } else {
            return $merchant->delete($params);
        }
    }

    public function actionSecret($id) {
        if (yii::$app->request->isPut) {
            $merchant = new MerchantModel();
            $params['id'] = $id;
            $params['app_secret'] = $this->get_randomstr(32);
            if (!isset($params['id'])) {
                $array = json_encode(['status' => 400, 'message' => '缺少参数 id',], JSON_UNESCAPED_UNICODE);
            } else {
                $array = $merchant->update($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
