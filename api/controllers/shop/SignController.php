<?php

namespace app\controllers\shop;

use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\SignModel;
use app\models\shop\SignInModel;
use app\models\core\TableModel;
use EasyWeChat\Factory;
use app\models\core\CosModel;
use app\models\shop\SignPrizeModel;
use app\models\core\UploadsModel;
use app\models\shop\UserModel;
use app\controllers\common\CommonController;
use app\models\shop\ScoreModel;
use app\models\shop\VoucherModel;
use app\models\shop\VoucherTypeModel;
use WxPay\Wechat;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class SignController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['signin', 'notify'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
    ];

    public function actionList($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $params['sign_id'] = $id;
            unset($params['id']);
            $model = new SignModel();
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SignModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $params['id'] = $id;
            $array = $model->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SignModel();
            $today_start = strtotime(date('Y-m-d 00:00:00'));
            $today_end = strtotime(date('Y-m-d 23:59:59'));
            $sign = $model->findall(['sign_id' => $id, 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id'], '`key`' => yii::$app->session['key'], "create_time>={$today_start} and create_time<={$today_end}" => null]);
            return $sign;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SignModel();

            //设置类目 参数
            $must = ['sign_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];

            $today_start = strtotime(date('Y-m-d 00:00:00'));
            $today_end = strtotime(date('Y-m-d 23:59:59'));
            $sign = $model->findall(['sign_id' => $params['sign_id'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => $params['user_id'], '`key`' => yii::$app->session['key'], "create_time>={$today_start} and create_time<={$today_end}" => null]);

            if ($sign['status'] == 200) {
                return result(500, '今天已签到');
            }
            if ($sign['status'] == 500) {
                return result(500, '请求失败');
            }

            $signIn = new SignInModel();
            $res = $signIn->find(['id' => $params['sign_id'], 'merchant_id' => yii::$app->session['merchant_id'], '`key`' => yii::$app->session['key']]);
            if ($res['status'] != 200) {
                return result(500, '找不到该活动');
            }

            $params['create_time'] = time();
            $array = $model->add($params);

            $sql = "update shop_user set score = score+" . $res['data']['integral'] . " where id = " . yii::$app->session['user_id'];
            Yii::$app->db->createCommand($sql)->execute();

            $number = $this->prize(yii::$app->session['merchant_id'], $params['sign_id'], yii::$app->session['user_id']);


            $continuous_arr = $res['data']['continuous_arr'];

            $arr = array();

            for ($i = 0; $i < count($continuous_arr); $i++) {
                if ($number == $continuous_arr[$i]['days']) {
                    $arr['`key`'] = yii::$app->session['key'];
                    $arr['merchant_id'] = yii::$app->session['merchant_id'];
                    $arr['user_id'] = yii::$app->session['user_id'];
                    $arr['sign_id'] = $params['sign_id'];
                    $arr['days'] = $number;
                    $arr['give_type'] = $continuous_arr[$i]['give_type'];
                    $arr['give_value'] = $continuous_arr[$i]['give_value'];
                    $arr['days'] = $number;
                    if ($continuous_arr[$i]['give_type']== 3) {
                        $arr['status'] = 0;
                    } else {
                        $arr['status'] = 1;
                    }
                    $signPrize = new SignPrizeModel();
                    $signPrizeData = $signPrize->findall($arr);
                    if ($signPrizeData['stauts'] == 204) {
                        $signPrize->add($arr);
                        $give_value = explode("_", $continuous_arr[$i]['give_value']);
                        $this->addPrize($continuous_arr[$i]['give_type'], $give_value[0], yii::$app->session['user_id'], yii::$app->session['key'], yii::$app->session['merchant_id']);
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSignin()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['`key`'] = $params['key'];
            $merchant_id = 13;
            unset($params['key']);
            $model = new SignInModel();
            $time = time();
            $params["start_time < {$time}"] = null;
            $params["end_time > {$time}"] = null;
            $params['status'] = 1;
            $array = $model->findall($params);
            $table = new TableModel();
            $sql = "SELECT COUNT(DISTINCT user_id) as num  FROM shop_sign where `key` = '{$params['`key`']}' and merchant_id  ={$merchant_id}";
            $res = $table->querySql($sql);

            $array['number'] = $res[0]['num'];
            $sql = "SELECT DISTINCT (user_id),avatar  FROM shop_sign INNER JOIN  shop_user on shop_user.id = shop_sign.user_id  where shop_sign.`key` = '{$params['`key`']}' and shop_sign.merchant_id  ={$merchant_id}";
            $res = $table->querySql($sql);
            $array['avatar'] = $res;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSign($id)
    {
        $key = yii::$app->session['key'];
        $merchant_id = yii::$app->session['merchant_id'];
        $user_id = yii::$app->session['user_id'];
        $model = new SignInModel();
        $res = $model->find(['id' => $id, 'merchant_id' => $merchant_id, '`key`' => $key]);
        $table = new TableModel();
        $sql = "select count(*)as num  from shop_sign where `key` ='{$key}' and merchant_id={$merchant_id} and user_id = {$user_id} and sign_id = {$id} and  create_time >= {$res['data']['start']} and create_time <= {$res['data']['end']}";
        $leiji = $table->querySql($sql);
        $signModel = new SignModel();
        $res = $signModel->findall(['merchant_id' => $merchant_id, '`key`' => "{$key}", 'user_id' => yii::$app->session['user_id'], 'sign_id' => $id, 'orderby' => ' create_time asc']);
        if ($res['status'] != 200) {
            $arr['qcode'] = $this->qcode($key);
            $arr['leiji'] = 1;
            $arr['lianxu'] = 1;
            return result(200, '请求成功', $arr);
        }
        $number = 1;
        if ($res['status'] != 200) {
            $number = 0;
        }
        if (count($res['data']) == 1) {
            $number = 1;
        } else {
            for ($i = 0; $i < count($res['data']); $i++) {
                if ($i + 1 < count($res['data'])) {
                    if (date('Y-m-d', $res['data'][$i]['time'] + (1 * 24 * 60 * 60)) == date('Y-m-d', $res['data'][$i + 1]['time'])) {
                        $number = $number + 1;
                    }
                } else if ($i == count($res['data']) && date('Y-m-d', $res['data'][$i]['time'] - (1 * 24 * 60 * 60)) == date('Y-m-d', $res['data'][$i - 1]['time'])) {
                    $number = $number + 1;
                }
            }
        }
        $arr['qcode'] = $this->qcode($key);
        $arr['leiji'] = $leiji[0]['num'];
        $arr['lianxu'] = $number;
        return result(200, '请求成功', $arr);
    }

    /*
     * 小程序二维码
     */

    public function qcode($key)
    {

        $config = $this->getSystemConfig($key, "miniprogram");
        if ($config == false) {
            return "";
        }
        $miniProgram = Factory::miniProgram($config);
        $response = $miniProgram->app_code->getUnlimit($key, ['width' => 280, "path" => '/pages/index/index/index']);

        // $url = getConfig('qrcode'.$key);
        //if($url==false){
        $url = "";
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $url = "";
            $filename = $response->saveAs(yii::getAlias('@webroot/') . "/uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/", time() . $config['app_id'] . rand(1000, 9999) . ".png");
            $localRes = "./uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/" . $filename;
            $cos = new CosModel();
            $cosRes = $cos->putObject($localRes);

            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
                unlink(Yii::getAlias('@webroot/') . $localRes);
            } else {
                $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/web/" . $localRes;
            }
            //  setConfig('qrcode'.$key ,$url);
        }
        //  }
        return $url;
    }

    public function actionTotal($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SignModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['id'] = $id;

            $model = new SignInModel();
            $res = $model->find(['id' => $data['id'], 'merchant_id' => $data['merchant_id'], '`key`' => $data['`key`']]);


            $table = new TableModel();
            $today_start = strtotime(date('Y-m-d 00:00:00'));
            $today_end = strtotime(date('Y-m-d 23:59:59'));
            $sql = "SELECT user_id,shop_user.nickname,shop_user.avatar,date_format(from_unixtime(shop_sign.create_time),'%Y-%m-%d %h:%m:%s') as time FROM  shop_sign  inner join shop_user on shop_user.id =  shop_sign.user_id where sign_id  = {$data['id']} and shop_sign.`key`='{$data['`key`']}' and shop_sign.merchant_id = {$data['merchant_id']} and  shop_sign.create_time >= {$today_start} and shop_sign.create_time <= {$today_end} order by shop_sign.create_time limit 0,20 ";
            $zaoqi = $table->querySql($sql);

            $sql = "select count(*)as num,shop_user.nickname,shop_user.avatar  from shop_sign inner join shop_user on shop_user.id =  shop_sign.user_id where shop_sign.`key` ='{$data['`key`']}' and shop_sign.merchant_id={$data['merchant_id']}  and sign_id = {$id} and  shop_sign.create_time >= {$res['data']['start']} and shop_sign.create_time <= {$res['data']['end']} group by user_id order by num desc limit 0,20";
            $leiji = $table->querySql($sql);


            $signModel = new SignModel();
            $sign = $signModel->findall(['merchant_id' => $data['merchant_id'], 'sign_id' => $data['id'], 'orderby' => 'create_time asc ']);

            $sql = "select user_id,shop_user.nickname,shop_user.avatar  from shop_sign inner join shop_user on shop_user.id =  shop_sign.user_id where shop_sign.`key` ='{$data['`key`']}' and shop_sign.merchant_id={$data['merchant_id']}  and sign_id = {$id} and  shop_sign.create_time >= {$res['data']['start']} and shop_sign.create_time <= {$res['data']['end']} group by user_id ";
            $user = $table->querySql($sql);

            $number = 1;


            $arr = array();
            $num = array();

            if ($sign['status'] == 200) {
                for ($i = 0; $i < count($user); $i++) {
                    for ($j = 0; $j < count($sign['data']); $j++) {
                        if ($user[$i]['user_id'] == $sign['data'][$j]['user_id']) {
                            $arr[$i][] = $sign['data'][$j];
                        }
                    }
                }
                for ($i = 0; $i < count($user); $i++) {
                    $number = 1;
                    for ($j = 0; $j < count($arr[$i]); $j++) {
                        if ($j + 1 < count($arr[$i])) {
                            if (date('Y-m-d', $arr[$i][$j]['time'] + (1 * 24 * 60 * 60)) == date('Y-m-d', $arr[$i][$j + 1]['time'])) {
                                $number = $number + 1;
                            } else {
                                $number = 1;
                            }
                        } else if ($j == count($arr[$i]) && date('Y-m-d', $arr[$i][$j]['time'] - (1 * 24 * 60 * 60)) == date('Y-m-d', $arr[$i][$j - 1]['time'])) {
                            $number = $number + 1;
                        } else if ($j == count($arr[$i]) && date('Y-m-d', $arr[$i][$j]['time'] - (1 * 24 * 60 * 60)) != date('Y-m-d', $arr[$i][$j - 1]['time'])) {
                            $number = 1;
                        }
                    }

                    $num[$i]['num'] = $number;
                    $num[$i]['user_id'] = $user[$i]['user_id'];
                    $num[$i]['nickname'] = $user[$i]['nickname'];
                    $num[$i]['avatar'] = $user[$i]['avatar'];
                }
            }
            for ($i = 0; $i < count($leiji); $i++) {
                for ($j = count($leiji) - 1; $j > $i; $j--) {
                    if ($leiji[$j] > $leiji[$j - 1]) {
                        $temp = $leiji[$j];
                        $leiji[$j] = $leiji[$j - 1];
                        $leiji[$j - 1] = $temp;
                    }
                }
            }
            $temp = array();
            for ($i = 0; $i < count($num); $i++) {
                for ($j = count($num) - 1; $j > $i; $j--) {
                    if ($num[$j] > $num[$j - 1]) {
                        $temp = $num[$j];
                        $num[$j] = $num[$j - 1];
                        $num[$j - 1] = $temp;
                    }
                }
            }

            $array['early'] = $zaoqi;
            $array['total'] = $leiji;
            $array['series'] = $num;
            return result(200, '请求成功', $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function prize($merchant_id, $id, $user_id)
    {
        $signModel = new SignModel();
        $res = $signModel->findall(['merchant_id' => $merchant_id, 'user_id' => $user_id, 'sign_id' => $id, 'orderby' => ' create_time asc']);
        $number = 1;
        if ($res['status'] != 200) {
            $number = 0;
        }
        if (count($res['data']) == 1) {
            $number = 1;
        } else {
            for ($i = 0; $i < count($res['data']); $i++) {
                if ($i + 1 < count($res['data'])) {
                    if (date('Y-m-d', $res['data'][$i]['time'] + (1 * 24 * 60 * 60)) == date('Y-m-d', $res['data'][$i + 1]['time'])) {
                        $number = $number + 1;
                    } else {
                        $number = 1;
                    }
                } else if ($i == count($res['data']) && date('Y-m-d', $res['data'][$i]['time'] - (1 * 24 * 60 * 60)) == date('Y-m-d', $res['data'][$i - 1]['time'])) {
                    $number = $number + 1;
                } else if ($i == count($res['data']) && date('Y-m-d', $res['data'][$i]['time'] - (1 * 24 * 60 * 60)) != date('Y-m-d', $res['data'][$i - 1]['time'])) {
                    $number = 1;
                }
            }
        }
        return $number;
    }

    public function actionIndex($id)
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象 $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $user_id = yii::$app->session['user_id'];

            $model = new SignInModel();
            $res = $model->find(['id' => $id, 'merchant_id' => $merchant_id, '`key`' => $key]);

            if ($res['status'] != 200) {
                return result('500', '补签失败，找不到活动');
            }
            $signModel = new SignModel();
            $stime = strtotime($params['time']);
            $etime = strtotime($params['time'] . " 23:59:59");
            $signData = array(
                "`key`" => yii::$app->session['key'],
                "merchant_id" => yii::$app->session['merchant_id'],
                "sign_id" => $id,
                "user_id" => yii::$app->session['user_id'],
                "status" => 2,
                "create_time >={$stime} and create_time <= {$etime}" => null,
            );
            $sign = $signModel->findall($signData);
            if ($sign['status'] == 200) {
                return result(500, "今天已补签！");
            }
            if ($sign['status'] == 500) {
                return $sign;
            }

//
            $table = new TableModel();
            $sql = "select count(*) as num  from shop_sign where status =2 and user_id = {$user_id}  and merchant_id = {$merchant_id} and `key` = '{$key}'";
            $rs = $table->querySql($sql);

            if ($rs[0]['num'] >= $res['data']['supplementary_number']) {
                return result(500, "最多补签{$res['data']['supplementary_number']}次");
            }


            $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogrampay");
            if ($config == false) {
                return result(500, "未配置小程序信息");
            }
//
            $name = "补签";

            //获取下单用户opid
            $userModel = new UserModel;
            $userData = $userModel->find(['id' => yii::$app->session['user_id']]);
            if ($userData['status'] != 200) {
                return result('500', '下单失败，找不到用户信息');
            }

            $payment = Factory::payment($config);
            $signData = array(
                "`key`" => yii::$app->session['key'],
                "merchant_id" => yii::$app->session['merchant_id'],
                "sign_id" => $id,
                "user_id" => yii::$app->session['user_id'],
                "pic_url" => "",
                "status" => 2,
                'create_time' => strtotime($params['time']),
            );


            $wxPayData = array(
                'body' => $name,
                'attach' => json_encode($signData, JSON_UNESCAPED_UNICODE),
                'out_trade_no' => time() . rand(1000, 9999),
                //   'total_fee' => (int)$res['data']['supplementary_price'] * 100,
                'total_fee' => (int)$res['data']['supplementary_price'],
                'notify_url' => "https://api2.juanpao.com/shop/sign/notify",
                'trade_type' => 'JSAPI',
                'openid' => $userData['data']['open_id'],
            );

            $rs = $payment->order->unify($wxPayData);

            if ($rs['return_code'] == "SUCCESS") {
                $jssdk = $payment->jssdk;
                $payinfo = $jssdk->bridgeConfig($rs['prepay_id'], false); // 返回数组
                return result(200, "请求成功", $payinfo);
            } else {
                return result(500, "下单失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionNotify()
    {
        //获取商户微信配置
        $xml = file_get_contents("php://input");
        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $returnValues = $wxPatNotify->GetValues();
        $result = $wxPatNotify->FromXml($xml);
        //    $result = json_decode('{"appid":"wxe8bceb47d563824d","attach":"{\"`key`\":\"wLCSUf\",\"merchant_id\":\"13\",\"sign_id\":\"1\",\"user_id\":\"90\",\"pic_url\":\"\",\"status\":2,\"create_time\":1552752000}","bank_type":"CFT","cash_fee":"1","fee_type":"CNY","is_subscribe":"N","mch_id":"1496441282","nonce_str":"5c909f18147c4","openid":"oQiQX0W1jfF6GdhDPdsEKYSVSAK0","out_trade_no":"15529817842897","result_code":"SUCCESS","return_code":"SUCCESS","sign":"7AD4908CB172517D25A8152A761FA507","time_end":"20190319154958","total_fee":"1","trade_type":"JSAPI","transaction_id":"4200000278201903193302384974"}', true);

        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //商户逻辑处理，如订单状态更新为已支付
            $params = json_decode($result['attach'], true);
            $signModel = new SignModel();
            $rs = $signModel->add($params);


            $model = new SignInModel();
            $res = $model->find(['id' => $params['sign_id'], 'merchant_id' => $params['merchant_id'], '`key`' => $params['`key`']]);

            $number = $this->prize($params['merchant_id'], $params['sign_id'], $params['user_id']);

            $continuous_arr = $res['data']['continuous_arr'];

            $arr = array();

            for ($i = 0; $i < count($continuous_arr); $i++) {
                if ($number == $continuous_arr[$i]['days']) {
                    $arr['`key`'] = yii::$app->session['key'];
                    $arr['merchant_id'] = yii::$app->session['merchant_id'];
                    $arr['user_id'] = yii::$app->session['user_id'];
                    $arr['sign_id'] = $params['sign_id'];
                    $arr['days'] = $number;
                    $arr['give_type'] = $continuous_arr[$i]['give_type'];
                    $arr['give_value'] = $continuous_arr[$i]['give_value'];
                    $arr['days'] = $number;
                    if ($continuous_arr[$i]['give_type']== 3) {
                        $arr['status'] = 0;
                    } else {
                        $arr['status'] = 1;
                    }
                    $signPrize = new SignPrizeModel();
                    $signPrizeData = $signPrize->findall($arr);
                    if ($signPrizeData['status'] == 204) {
                        $signPrize->add($arr);
                        $give_value = explode("_", $continuous_arr[$i]['give_value']);
                        $this->addPrize($continuous_arr[$i]['give_type'], $give_value[0], yii::$app->session['user_id'], yii::$app->session['key'], yii::$app->session['merchant_id']);
                    }
                }
            }

            if ($rs['status'] == 200) {
                ob_clean();
                echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                die();
            } else {
                ob_clean();
                echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
                die();
            }
        }
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

    public function addPrize($give_type, $give_value, $user_id, $key, $merchant_id)
    {

        if ($give_type == 1) {
            $sql = "update shop_user set score = score+{$give_value} where id = {$user_id}";
            yii::$app->db->createCommand($sql)->execute();

            $scoreModel = new ScoreModel();
            $params = array(
                '`key`' => $key,
                'merchant_id' => $merchant_id,
                'user_id' => $user_id,
                'score' => $give_value,
                'content' => '签到送积分',
                'type' => '1',
                'status' => '1'
            );
            $scoreModel->add($params);
        } else if ($give_type == 2) {
            $voucher = new VoucherModel();
            $cc = new CommonController();
            $params['cdkey'] = $cc->generateCode();
            $params['`key`'] = $key;
            $params['merchant_id'] = $merchant_id;
            $params['user_id'] = $user_id;
            $params['type_id'] = $give_value;
            //获取优惠券类型
            $type = new VoucherTypeModel();
            $typedata['id'] = $params['type_id'];
            $voutype = $type->find($typedata);


            //优惠券新增参数
            $vdata['cdkey'] = $params['cdkey'];
            $vdata['type_id'] = $params['type_id'];
            $vdata['type_name'] = $voutype['data']['name'];
            $vdata['status'] = 1;
            $vdata['start_time'] = time();
            $vdata['end_time'] = $voutype['data']['to_date1'];
            $vdata['is_exchange'] = 0;
            $vdata['merchant_id'] = $params['merchant_id'];

            $vdata['`key`'] = $params['`key`'];
            $vdata['is_used'] = 0;
            $vdata['price'] = $voutype['data']['price'];
            $vdata['full_price'] = $voutype['data']['full_price'];
            $vdata['user_id'] = $params['user_id'];
            $array = $voucher->add($vdata);
            //更新优惠券个数
            $typeparams['send_count'] = $voutype['data']['send_count'] + 1;
            $typeparams['id'] = $params['type_id'];
            $type->update($typeparams);
        }
    }

}
