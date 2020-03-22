<?php

namespace app\controllers\merchant\pay;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\pay\PayModel;
use app\models\merchant\app\ComboModel;
use app\models\merchant\app\AppAccessModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\system\SystemWxConfigModel;
use app\models\forum\UserModel;
use app\models\merchant\app\AppModel;
use app\models\forum\ForumModel;
use app\models\merchant\user\MerchantModel;
use app\models\wolive\BusinessModel;
use app\models\wolive\ServiceModel;
use app\models\merchant\system\GroupModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class PayController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppAccessModel();

            $data['mid'] = yii::$app->session['uid'];
            $array = $app->finds($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppModel();
            $array = $app->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $app = new AppModel();
            $params['id'] = $id;
            $array = $app->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $appAccess = new AppAccessModel();
            //设置类目 参数
            $must = ['name', 'pic_url', 'app_id', 'combo_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            $app = new \app\models\merchant\user\MerchantModel();
            $data['id'] = yii::$app->session['uid'];
            $array = $app->one($data);

            $a['merchant_id'] = yii::$app->session['uid'];
            $a['status'] = 1;
            $acc = $appAccess->find($a);

            if($acc['status']==200){
                if(count($acc['data'])>=$array['data']['number']){
                     return result(500, "您的应用已经超过限制次数");
                }
            }

            $params['merchant_id'] = yii::$app->session['uid'];
            $data['`key`'] = generate();

            $appa = $appAccess->find($data);
            if ($appa['status'] == 200) {
                return result(500, "请稍后在提交订单");
            }
            // 购买应用上传图片
            $base = new Base64Model();
            $str = creat_mulu("./uploads/merchantapp");
            $localRes = $base->base64_image_content($params['pic_url'], $str);
            if (!$localRes) {
                return result(500, "图片格式错误");
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosRes = $cos->putObject($localRes);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
            } else {
                unlink(Yii::getAlias('@webroot/') . $localRes);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            $params['expire_time'] = "";
            $params['type'] = "";
            $params['key'] = $data['`key`'];
            $params['pic_url'] = $url;
            $transaction = Yii::$app->db->beginTransaction();
            $appsRs = $appAccess->add($params);
            $num = 6 - strlen($appsRs['data']);

            $str = "";
            for ($i = 0; $i < $num; $i++) {
                if ($i == 0) {
                    $str = "0";
                } else {
                    $str = $str . "0";
                }
            }
            $res = $appAccess->upd(['`key`' => $str . $appsRs['data'], 'id' => $appsRs['data']]);
            if ($appsRs['status'] != 200) {
                $transaction->rollBack(); //回滚
                return result(500, '订单创建失败！');
            } else {

                $comboModel = new ComboModel;
                $comboData['id'] = $params['combo_id'];
                $combo = $comboModel->find($comboData);
                if ($combo['status'] != 200) {
                    $transaction->rollBack(); //回滚
                    return result(500, "对不起没又该套餐！");
                }

                $pay['app_access_id'] = $appsRs['data'];
                $pay['merchant_id'] = $params['merchant_id'];
                $pay['remain_price'] = $combo['data']['money'];
                $pay['type'] = 0;
                $pay['status'] = 2;
                $pay['create_time'] = time();
                $payModel = new PayModel();
                $payrs = $payModel->add($pay);
                if ($payrs['status'] != 200) {
                    return result(500, '订单创建失败！');
                }
                //    var_dump($combo['data']['money']);
                $payrs['money'] = $combo['data']['money'];
                $money = 0.00;
                if ((float) $payrs['money'] === (float) $money) {
                    $rs = $this->updateOrder($payrs['data'], $params['combo_id']);
                }
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                return $payrs;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function updateOrder($out_trade_no, $comid) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $payModel = new PayModel();
            $params['id'] = $out_trade_no;
            $params['type'] = 2;
            $params['status'] = 0;
            $params['pay_time'] = time();
            $payModel->update($params);


            $payinfo = $payModel->find(['id' => $out_trade_no]);
            $comboModel = new ComboModel();
            $comboinfo = $comboModel->find(['id' => $comid]);
//            $data['expire_time'] = strtotime(date('Y-m-d', strtotime("+{$comboinfo['data']['expired_days']}day")));
//            $data['id'] = $payinfo['data']['app_access_id'];
//
//            $appAccessModel = new AppAccessModel();
//            $rs = $appAccessModel->update($data);
            if ($comboinfo['data']['app_id'] == 1) {
                $appAccessModel = new AppAccessModel();
                $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);

                $comboModel = new ComboModel();
                $comboinfo = $comboModel->find(['id' => $appAccess['data']['combo_id']]);
                $data['expire_time'] = "";
                $data['id'] = $payinfo['data']['app_access_id'];
                $data['status'] = 1;
                $rs = $appAccessModel->update($data);
                unset($data['expire_time']);
                unset($data['status']);

                // $apppAccess = $appAccessModel->find($data);
                $forumModel = new ForumModel();
                $config = array(
                    'must_keyword' => 0,
                    'must_examine' => 0,
                    'allow_post_time' => 0,
                    'allow_comment_level' => 0,
                    'illegally' => "",
                    'score' => false,
                );

                $array = array(
                    '`key`' => $appAccess['data']['key'],
                    'name' => $appAccess['data']['name'],
                    'merchant_id' => $appAccess['data']['merchant_id'],
                    'pic_url' => $appAccess['data']['pic_url'],
                    'detail_info' => $appAccess['data']['detail_info'],
                    'config' => json_encode($config),
                    'status' => 1,
                );
                $forumModel->add($array);

                $foromUserModel = new UserModel();
                $userdata = array(
                    '`key`' => $appAccess['data']['key'],
                    'avatar' => $appAccess['data']['pic_url'],
                    'merchant_id' => $appAccess['data']['merchant_id'],
                    'nickname' => '管理员',
                    'sex' => '1',
                    'is_admin' => 9,
                    'status' => 1
                );
                $foromUserModel->add($userdata);

                $systemConfigModel = new SystemWxConfigModel();
                $systemConfigdata['merchant_id'] = $appAccess['data']['merchant_id'];
                $systemConfigdata['`key`'] = $appAccess['data']['key'];
                $systemConfigdata['wechat'] = json_encode(array(
                    "type" => 0,
                    "wechat_id" => 0,
                    "app_id" => "",
                    "url" => "https://api2.juanpao.com/wx?key={$appAccess['data']['key']}",
                    "secret" => "",
                    "token" => generateCode(32),
                    "aes_key" => generateCode(43)
                ));
                $systemConfigdata['wechat_pay'] = json_encode(array(
                    "type" => 0,
                    "app_id" => "",
                    "mch_id" => "",
                    "cert_path" => "",
                    "key_path" => "",
                    'notify_url' => "http://api2.juanpao.com/pay/wechat/notify",
                ));
                $systemConfigdata['miniprogram'] = "";
                $systemConfigdata['wechat_info'] = '{"name":"","app_id":"",app_secret:0,"account":"","type":"","describe":"","wechat_id":"","head_img":"","qrcode_url":""}';
                $systemConfigModel->add($systemConfigdata);
            } else if ($comboinfo['data']['app_id'] == 2) {

                $appAccessModel = new AppAccessModel();
                $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);
                $comboModel = new ComboModel();
                $comboinfo = $comboModel->find(['id' => $appAccess['data']['combo_id']]);
                $data['expire_time'] = strtotime(date('Y-m-d', strtotime("+{$comboinfo['data']['expired_days']}day")));
                $data['id'] = $payinfo['data']['app_access_id'];
                $data['status'] = 1;
                $data['config'] = '{"is_large_scale":"1","number":"100000"}';
                $rs = $appAccessModel->update($data);

                $systemConfigModel = new SystemWxConfigModel();
                $systemConfigdata['merchant_id'] = $appAccess['data']['merchant_id'];
                $systemConfigdata['`key`'] = $appAccess['data']['key'];
                $systemConfigdata['wechat'] = json_encode(array(
                    "type" => 0,
                    "wechat_id" => 0,
                    "app_id" => "",
                    "secret" => "",
                    "url" => "https://api2.juanpao.com/wx?key={$appAccess['data']['key']}",
                    "token" => generateCode(32),
                    "aes_key" => generateCode(43)
                ));
                $systemConfigdata['wechat_pay'] = json_encode(array(
                    "type" => 0,
                    "app_id" => "",
                    "mch_id" => "",
                    "cert_path" => "",
                    "key_path" => "",
                    'notify_url' => "http://api2.juanpao.com/pay/wechat/notify",
                ));
                $systemConfigdata['miniprogram'] = "";
                $systemConfigModel->add($systemConfigdata);


                $merchantModel = new MerchantModel();
                $merchant = $merchantModel->find(['id' => $appAccess['data']['merchant_id']]);

                $businessModel = new BusinessModel();
                $bdata = array(
                    'business_id' => $appAccess['data']['key'],
                    'video_state' => 'close',
                    'voice_state' => 'open',
                    'audio_state' => 'open',
                    'distribution_rule' => 'auto',
                    'voice_address' => '/upload/voice/default.mp3',
                    'state' => 'open'
                );
                $businessModel->add($bdata);

                $serviceModel = new ServiceModel();
                $sdata = array(
                    'user_name' => $appAccess['data']['key'],
                    'nick_name' => $appAccess['data']['name'],
                    'real_name' => $merchant['data']['real_name'],
                    'password' => $merchant['data']['password'],
                    'salt' => $merchant['data']['salt'],
                    'groupid' => '0',
                    'phone' => $merchant['data']['phone'],
                    'email' => '',
                    'business_id' => $appAccess['data']['key'],
                    'avatar' => $appAccess['data']['pic_url'],
                    'level' => 'super_manager',
                    'parent_id' => '0',
                    'state' => 'offline',
                );
                $serviceModel->add($sdata);

                $groupModel = new GroupModel();
                $rdata = array(
                    'key' => $appAccess['data']['key'],
                    'merchant_id' => $appAccess['data']['merchant_id'],
                    'title' => '客服',
                    'status' => 1,
                    'create_time' => time(),
                    'is_kefu' => 1,
                );
                $groupModel->add($rdata);


                $merchatComboModel = new \app\models\merchant\system\MerchantComboModel();
                $combo = $merchatComboModel->do_one(['type' => 9]);

                $merchatComboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
                $order = "combo_" . date("YmdHis", time()) . rand(1000, 9999);
                $comboData = array(
                    'merchant_id' => $appAccess['data']['merchant_id'],
                    'key' => $appAccess['data']['key'],
                    'order_sn' => $order,
                    'combo_id' => $params['id'],
                    'sms_number' => $combo['data']['sms_number'],
                    'order_number' => $combo['data']['order_number'],
                    'sms_remain_number' => $combo['data']['sms_number'],
                    'order_remain_number' => $combo['data']['order_number'],
                    'validity_time' => strtotime(date('Y-m-d', strtotime("+ 12month"))),
                    'type' => $combo['data']['type'],
                    'remarks' => "购买应用赠送",
                    'status' => 1,
                );

                $res = $merchatComboAccessModel->do_add($comboData);
            }
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            return result(200, "更新成功");
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "更新失败");
        }
        return $rs;
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $app = new AppModel();
            $base = new Base64Model();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
//                $data = [
//                    'id' => $params['id'],
//                    'name' => $params['name'],
//                    'category_id' => $params['category_id'],
//                    'pic_url' => '',
//                    'detail_info' => isset($params['detail_info']) ? $params['detail_info'] : "",
//                    'type' => $params['type'],
//                    'parent_id' => isset($params['parent_id']) ? $params['parent_id'] : "",
//                    'status' => isset($params['status']) ? $params['status'] : "",
//                    'update_time' => time(),
//                ];
                if (isset($params['pic_url'])) {
                    $str = creat_mulu("./uploads/app");
                    $params['pic_url'] = $base->base64_image_content($params['pic_url'], $str);
                    //将图片上传到cos
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($params['pic_url']);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    $params['pic_url'] = $url;
                }
                $array = $app->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete() {
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

}
