<?php

namespace app\controllers\pay;

use yii;
use yii\web\Controller;
use yii\db\Exception;
use alipay\Alipay;
use alipay\AlipayTradeService;
use app\models\merchant\app\AppAccessModel;
use app\models\merchant\pay\PayModel;
use app\models\merchant\app\ComboModel;
use app\models\merchant\forum\ForumModel;
use app\models\pay\AlipayModel;
use app\models\merchant\user\UserModel;
use app\models\admin\app\AppModel;

require_once yii::getAlias('@vendor/aliwappay/config.php');
require_once yii::getAlias('@vendor/aliwappay/wappay/service/AlipayTradeService.php');
require_once yii::getAlias('@vendor/aliwappay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php');

/**
 * 阿里支付控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class AliwappayController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/阿里支付/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionIndex($id) {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params['id'] = $id;

            if (!isset($params['id'])) {
                return result(500, "缺少请求参数 订单号");
            }
            $payModel = new PayModel();
            $payinfo = $payModel->find($params);
            if ($payinfo['status'] != 200) {
                return result(500, "无效订单号");
            }
            if ($payinfo['data']['status'] != 2) {
                return result(500, "无效订单");
            }
            $appAccessModel = new AppAccessModel();
            $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);
            if ($appAccess['status'] != 200) {
                return result(500, "套餐无效");
            }
            $comboModel = new ComboModel();
            $combo = $comboModel->find(['id' => $appAccess['data']['combo_id']]);
            if ($combo['status'] != 200) {
                return result(500, "套餐已下架");
            }
            $appModel = new AppModel();
            $app = $appModel->find(['id' => $appAccess['data']['app_id']]);
            if ($app['status'] == 200) {
                if ($app['data']['category_id'] == 1) {
                    $out_trade_no = "forum_" . $params['id'];
                } else if ($app['data']['category_id'] == 2) {
                    $out_trade_no = "shop_" . $params['id'];
                }
            } else {
                return result(500, "找不到APP信息");
            }
//            $data['WIDout_trade_no'] = //订单名称，必填
//                    $data['WIDsubject'] = $combo['data']['name'];
//            //付款金额，必填
            // $data['WIDtotal_amount'] = $payinfo['data']['remain_price'];
//            $data['WIDtotal_amount'] = 0.01;
//            //商品描述，可空
//            $data['WIDbody'] = $combo['data']['name'];
            //订单名称，必填
            $subject = $combo['data']['name'];

            //付款金额，必填
            // $total_amount = $payinfo['data']['remain_price'];
            $total_amount = 0.01;
            //商品描述，可空
            $body = $combo['data']['name'];

            //超时时间
            $timeout_express = "1m";

            $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setTimeExpress($timeout_express);
            $payResponse = new \AlipayTradeService(yii::$app->params['ali_config']);
            yii::$app->params['ali_config']['return_url'] = "http://192.168.188.71:8080/#/aliReturnUrl";
            $payResponse->wapPay($payRequestBuilder, yii::$app->params['ali_config']['return_url'], yii::$app->params['ali_config']['notify_url']);
            die();
        } else {
            return result(500, "请求方式错误");
        }
    }
}
