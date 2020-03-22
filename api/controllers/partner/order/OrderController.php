<?php

namespace app\controllers\partner\order;

use app\models\admin\app\AppAccessModel;
/*use app\models\admin\user\SystemAccessModel;
use app\models\shop\BalanceModel;
use app\models\shop\UserModel;
use app\models\system\SystemMerchantMiniAccessModel;
use foo\bar;
use tools\pay\Payx;*/
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\OrderModel;
/*use app\models\core\SMS\SMS;
use app\models\core\UploadsModel;
use app\models\core\CosModel;
use app\models\core\WxConfigModel;
use app\models\merchant\pay\PayModel;
use app\models\shop\SubOrderModel;
use EasyWeChat\Factory;
use app\models\shop\AfterInfoModel;
use app\models\shop\ElectronicsModel;
use app\models\shop\SystemExpressModel;*/

/*require_once yii::getAlias('@vendor/wxpay/Wechat.php');
include dirname(dirname(dirname(__DIR__))) . '/extend/tools/pay/Refund/Refund.php';
include dirname(dirname(dirname(__DIR__))) . '/extend/tools/pay/Pay.php';*/

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class OrderController extends yii\web\PartnerController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 订单概述
     * @return array
     */
    public function actionSummary()
    {
        if (yii::$app->request->isGet) {
            $request = request(); //获取地址栏参数
            $params = $request['params'];
            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['m_id'];
            $params['partner_id'] = yii::$app->session['partner_id'];

            $model = new OrderModel();
            $payment = 0; //待付款数量
            $delivery = 0; //待发货数量
            $evaluate = 0; //待评价数量
            $safeguardingRights = 0; //维权中数量
            $todayOrder = 0; //今日下单数量
            $todayPrice = 0; //今日订单金额
            $array = $model->findSummary($params);
            if ($array['status'] != 200) {
                return result(200, "请求成功");
            }
            $data = $array['data']; //订单列表
            $order = count($data); //订单数量
            $today = Date('Y-m-d'); //今日日期
            for ($i = 0; $i < $order; $i++) {
                $status = $data[$i]['status'];
                $afterSale = $data[$i]['after_sale'];
                $create_time = Date('Y-m-d', $data[$i]['create_time']);
                $payment_money = $data[$i]['payment_money'];
                if ($status == 0) {//待付款
                    $payment++;
                } else if ($status == 1) {//待发货
                    $delivery++;
                } else if ($status == 6) {//待评价
                    $evaluate++;
                }
                if ($afterSale !== '-1' && $status == 5) {//维权中
                    $safeguardingRights++;
                }
                if ($today == $create_time) {
                    $todayOrder++;
                    $todayPrice += $payment_money;
                }
            }

            for ($i = 0; $i <= 29; $i++) {
                $time = date("Y-m-d", strtotime("-{$i} day"));
                $startTime = strtotime($time . " 00:00:00");
                $params["create_time >={$startTime}"] = null;
                $endTime = strtotime($time . " 23:59:59");
                $params["create_time <={$endTime}"] = null;
                $orderArray = $model->findSummary($params);
                unset($params["create_time >={$startTime}"]);
                unset($params["create_time <={$endTime}"]);
                $out['day'][] = $time;
                if ($orderArray['status'] === 200) {
                    $dataCount = count($orderArray['data']);
                    //  $out['num'][] = $dataCount;
                    //循环获取价格
                    $outPrice = 0;
                    $out['num'][$i] = 0;
                    //status <> 2 and status <> 0 and status <>9
                    for ($j = 0; $j < $dataCount; $j++) {
                        if ($orderArray['data'][$j]['status'] != 2 && $orderArray['data'][$j]['status'] != 0 && $orderArray['data'][$j]['status'] != 9) {
                            $outPrice += $orderArray['data'][$j]['payment_money'];
                            $out['num'][$i] = $out['num'][$i] + 1;
                        }
                    }
                    $out['price'][] = sprintf("%.2f", substr(sprintf("%.3f", $outPrice), 0, -2));
                } else {
                    $out['num'][] = 0;
                    $out['price'][] = 0;
                }
            }

            $out['total'] = [
                'order' => $order,
                'payment' => $payment,
                'delivery' => $delivery,
                'evaluate' => $evaluate,
                'safeguardingRights' => $safeguardingRights,
                'todayOrder' => $todayOrder,
                'todayPrice' => $todayPrice,
            ];
            return result(200, "请求成功", $out);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 订单列表
     * @return array|false|string
     * @throws Exception
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new OrderModel();
            $params['shop_order_group.`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['m_id'];
            $params['shop_order_group.partner_id'] = yii::$app->session['partner_id'];
            $array = $model->findAll($params);

            if ($array['status'] == 200) {
                $areaModel = new \app\models\system\SystemAreaModel();
                for ($i = 0; $i < count($array['data']); $i++) {
                    if ($array['data'][$i]['province_code'] != null && $array['data'][$i]['city_code'] != null && $array['data'][$i]['area_code'] != null) {
                        $province = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['province_code']]);
                        $city = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['city_code']]);
                        $area = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['area_code']]);
                        $array['data'][$i]['province'] = $province['data'][0];
                        $array['data'][$i]['city'] = $city['data'][0];
                        $array['data'][$i]['area'] = $area['data'][0];
                    } else {
                        $array['data'][$i]['province'] = "";
                        $array['data'][$i]['city'] = "";
                        $array['data'][$i]['area'] = "";
                    }
                }
            }

            //查询闪送查询是否开启
            $appModel = new AppAccessModel();
            $res = $appModel->find(['`key`'=>$params['shop_order_group.`key`']]);
            if ($res['status'] == 200) {
                $array['shansong'] = $res['data']['shansong'];
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
}
