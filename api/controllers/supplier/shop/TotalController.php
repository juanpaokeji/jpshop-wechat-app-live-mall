<?php

namespace app\controllers\supplier\shop;

use app\models\merchant\system\UserModel;
use app\models\shop\OrderModel;
use yii;
use yii\web\SupplierController;
use yii\db\Exception;
use app\models\core\TableModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TotalController extends SupplierController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\SupplierFilter', //调用过滤器
//                'only' => ['single'], //指定控制器应用到哪些动作
//                'except' => ['total'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }
    public $config = [
    ];

    /**
     * 周概况
     */
    public function actionTotal()
    {
        if (yii::$app->request->isGet) {

            $request = request(); //获取地址栏参数
            $params = $request['params'];
           // yii::$app->session['sid'] = 121;
            $data['today'] = $this->today();
            $data['total'] = $this->total();
            $data['balance'] = $this->sum();
            return result(200, "请求成功", $data);

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function today()
    {
        $table = new TableModel();
        $startTime = strtotime(date('Y-m-d'), time());
        $endTime = time();
        $sql = "select sum(payment_money) as payment_money  from  shop_order_group where create_time>{$startTime} and create_time <{$endTime}  and (status= 6 or status=7 or status = 1 or status = 3 )  and supplier_id = " . yii::$app->session['sid'] . ";";
        $money = $table->querySql($sql);

        $sql = "select count(id) as num  from  shop_order_group where create_time>{$startTime} and create_time <{$endTime} and  (status= 6 or status=7 or status = 1 or status = 3 ) and delete_time is null  and supplier_id = " . yii::$app->session['sid'] . " ;";
        $order = $table->querySql($sql);

        $data['today_turnover'] = $money[0]['payment_money'] == null ? 0 : (float)$money[0]['payment_money'];
        $data['today_order'] = (float)$order[0]['num'];
        $data['today_average_price'] = $data['today_order'] == 0 ? 0 : (float)number_format($data['today_turnover'] / $data['today_order'], 2);
        return $data;
    }

    public function total()
    {
        $table = new TableModel();
        $sql = "select sum(payment_money) as payment_money  from  shop_order_group where (status = 6 or status = 7 or status = 1 or status = 3 ) and supplier_id = " . yii::$app->session['sid'] . ";";
        $money = $table->querySql($sql);

        $sql = "select count(id) as num  from  shop_order_group where (status = 6 or status=7 or status = 1 or status = 3 ) and delete_time is null  and supplier_id = " . yii::$app->session['sid'] . ";";
        $order = $table->querySql($sql);
        $data['turnover'] = $money[0]['payment_money'] == null ? 0 : (float)$money[0]['payment_money'];

        $data['order'] = (float)$order[0]['num'];
        $data['price'] = $data['order'] == 0 ? 0 :(float) number_format($data['turnover'] / $data['order'], 2);
        return $data;
    }

    public function sum()
    {
        $table = new TableModel();
        $orderModel = new OrderModel();
        $order = $orderModel->findList(['supplier_id' => yii::$app->session['sid']]);
        $data['yue'] = 0;
        $data['koudian'] = 0;
        $data['shdtx'] = 0;
        $data['ljyjs'] = 0;
        $data['ljzctzyj'] = 0;
        $data['ljzckd'] = 0;
        if ($order['status'] != 200) {
            return result(200, $data);
        }

        $subUserModel = new UserModel();
        $user = $subUserModel->find(['id' => yii::$app->session['sid']]);

        $data['yue'] = (float)$user['data']['balance']-$user['data']['balance'] * ($user['data']['points'] / 100);
        $leader = json_decode($user['data']['leader'], true);
        $data['koudian'] = round($user['data']['balance'] * ($user['data']['points'] / 100),2);

        $sql = "select sum(money) as num from system_sub_admin_balance where status = 0 and type= 6";
        $res = $table->querySql($sql);
        $data['shdtx'] =(float) $res[0]['num'];

        $sql = "select sum(money) as num from system_sub_admin_balance where status = 1 and type= 6";
        $res = $table->querySql($sql);
        $data['ljyjs'] =(float) $res[0]['num'];

        $order_sn = "";

        for ($i = 0; $i < count($order['data']); $i++) {
            if ($i == 0) {
                $order_sn = "'{$order['data'][$i]['order_sn']}'";
            } else {
                $order_sn = $order_sn . "," . "'{$order['data'][$i]['order_sn']}'";
            }
        }

        $sql = "select sum(money) as num  from shop_user_balance  where type = 1 and order_sn in($order_sn);";
        $res = $table->querySql($sql);
        $data['ljzctzyj'] = (float)$res[0]['num'];

        $data['ljzckd'] =  number_format($data['shdtx'] * ($user['data']['points'] / 100), 2);
        return $data;
    }

}
