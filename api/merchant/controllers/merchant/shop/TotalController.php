<?php

namespace app\controllers\merchant\shop;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\core\TableModel;
use app\models\core\CosModel;
use EasyWeChat\Factory;
use tools\db\Redis;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TotalController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\ForumFilter', //调用过滤器
//                'only' => ['single'], //指定控制器应用到哪些动作
//                'except' => ['post'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }
    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
    ];

    /**
     * 周概况
     */
    public function actionTotal() {
        if (yii::$app->request->isGet) {

            $request = request(); //获取地址栏参数
            $params = $request['params'];
            $params['`key`'] = $params['key'];
           // setConfig($params['key'] . "total");
            $res = getRedis($params['key'] . "total");
            if ($res == false) {
                $params['merchant_id'] = yii::$app->session['uid'];
                //状态 0=待付款 1=待发货 2=已取消(24小时未支付) 3=已发货 4=已退款 5=退款中 6=待评价 7=已完成(评价后)  8=已删除  9一键退款
                $data['today'] = $this->today($params['key']);
                $data['yesterday'] = $this->today($params['key']);
                $data['week'] = $this->week($params['key']);
                $data['month'] = $this->month($params['key']);
                $data['matter'] = $this->matter($params['key']);
                $data['total_day'] = $this->total_day($params['key']);
                $data['total_month'] = $this->total_month($params['key']);
                $data['qcode'] = "http://tuan.weikejs.com/api/web/".$this->qcode($params['key']);
                setConfig($params['key'] . "total", $data, 300);
                return result(200, "请求成功", $data);
            } else {
                return result(200, "请求成功", $res);
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function today($key) {
        $table = new TableModel();
        $startTime = strtotime(date('Y-m-d'),time());
        $endTime = time();
        $sql = "select sum(payment_money) as payment_money  from  shop_order_group where create_time>{$startTime} and create_time <{$endTime}  and (status <> 2 and status <> 0 and status <>9) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $money = $table->querySql($sql);

        $sql = "select count(distinct ip) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
        $user = $table->querySql($sql);

//        $sql = "select count(distinct ip) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
//        $longin_user = $table->querySql($sql);

        $sql = "select count(id) as num  from  shop_order_group where create_time>{$startTime} and create_time <{$endTime} and (status !=2 or status != 0) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $order = $table->querySql($sql);
        $data['today_turnover'] = $money[0]['payment_money'] == null ? 0 : $money[0]['payment_money'];
        $data['today_visitor'] = $user[0]['num'] + $user[0]['num'];
        $data['today_order'] = $order[0]['num'];
        $data['today_average_price'] = $data['today_order'] == 0 ? 0 : number_format($data['today_turnover'] / $data['today_order'],2);
        return $data;
    }

    public function week($key) {
        $table = new TableModel();
        $sql = "select sum(payment_money) as payment_money  from  shop_order_group where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time)) and (status <> 2 and status <> 0 and status <>9) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $money = $table->querySql($sql);

        $sql = "select count(distinct ip) as num  from  system_log where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time))  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
        $user = $table->querySql($sql);

        $sql = "select count(distinct ip) as num  from  system_log where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time))  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
        $longin_user = $table->querySql($sql);

        $sql = "select count(id) as num  from  shop_order_group where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time)) and (status <> 2 and status <> 0 and status <>9 ) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $order = $table->querySql($sql);
        $data['seven_day_turnover'] = $money[0]['payment_money'] == null ? 0 : $money[0]['payment_money'];
        $data['seven_day_visitor'] = $user[0]['num'] + $longin_user[0]['num'];
        $data['seven_day_order'] = $order[0]['num'];
        $data['seven_day_average_price'] = $data['seven_day_order'] == 0 ? 0 : number_format($data['seven_day_turnover'] / $data['seven_day_order'],2);
        return $data;
    }

    public function month($key) {
        $table = new TableModel();
        $sql = "select sum(payment_money) as payment_money  from  shop_order_group where DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(FROM_UNIXTIME(create_time)) and (status <> 2 and status <> 0 and status <>9 ) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $money = $table->querySql($sql);

        $sql = "select count(distinct ip) as num  from  system_log where DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(FROM_UNIXTIME(create_time))  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
        $user = $table->querySql($sql);

        $sql = "select count(distinct ip) as num  from  system_log where DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(FROM_UNIXTIME(create_time))  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
        $longin_user = $table->querySql($sql);

        $sql = "select count(id) as num  from  shop_order_group where DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(FROM_UNIXTIME(create_time)) and (status <> 2 and status <> 0 and status <>9 ) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $order = $table->querySql($sql);
        $data['thirty_days_turnover'] = $money[0]['payment_money'] == null ? 0 : $money[0]['payment_money'];
        $data['thirty_days_visitor'] = $user[0]['num'] + $longin_user[0]['num'];
        $data['thirty_days_order'] = $order[0]['num'];
        $data['thirty_days_average_price'] = $data['thirty_days_order'] == 0 ? 0 : number_format($data['thirty_days_turnover'] / $data['thirty_days_order'],2);
        return $data;
    }

    public function matter($key) {
        $table = new TableModel();
        $sql = "select count(id) as num  from  shop_order_group where  status =1 and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $fahuo = $table->querySql($sql);
        //状态 0=待付款 1=待发货 2=已取消(24小时未支付) 3=已发货 4=已退款 5=退款中 6=待评价 7=已完成(评价后)  8=已删除  9一键退款
        $sql = "select count(id)as num  from  shop_order_group where status =5 and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $tuikuan = $table->querySql($sql);

        $sql = "select count(id) as num  from  shop_goods where  status =0 and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
        $xiajia = $table->querySql($sql);
        $data['un_shipped_order'] = $fahuo[0]['num'];
        $data['refund_order'] = $tuikuan[0]['num'];
        $data['warehouse_order'] = $xiajia[0]['num'];
        return $data;
    }

    public function total_month($key) {
        $data = array();
        $table = new TableModel();

        for ($i = 29; $i >= 0; $i--) {
            $data['day'][] = date("Y-m-d", strtotime("-{$i} day"));
        }

        $sql = "select sum(payment_money) as payment_money,FROM_UNIXTIME( create_time, '%Y-%m-%d' ) AS data_time  from  shop_order_group where create_time > UNIX_TIMESTAMP( date_sub( curdate( ), INTERVAL 29 DAY ) )  and (status <> 2 and status <> 0 and status <>9 ) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . " GROUP BY data_time";
        $money = $table->querySql($sql);
        $sql = "select count(*) as visit_num,count(distinct ip) as num,FROM_UNIXTIME( create_time, '%Y-%m-%d' ) AS data_time from  system_log where create_time > UNIX_TIMESTAMP( date_sub( curdate( ), INTERVAL 29 DAY ) )  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . " GROUP BY data_time;";
        $user = $table->querySql($sql);

        foreach ($data['day'] as $key=>$val){
            $data['turnover'][$key] = 0;
            $data['visitor'][$key] = 0;
            $data['visit'][$key] = 0;
            foreach ($money as $mk=>$mv){
                if ($mv['data_time'] == $val){
                    $data['turnover'][$key] = $mv['payment_money'];
                }
            }
            foreach ($user as $uk=>$uv){
                if ($uv['data_time'] == $val){
                    $data['visitor'][$key] = $uv['num'] + $uv['num'];
                    $data['visit'][$key] = $uv['visit_num'];
                }
            }
        }
        return $data;
        //原方法逻辑，SQL存疑
        /*for ($i = 30; $i > 0; $i--) {
            $time = date("Y-m-d", strtotime("-{$i} day"));
            $data['day'][] = $time;
            $startTime = strtotime($time . " 00:00:00");
            $endTime = strtotime($time . " 23:59:59");

            $sql = "select sum(payment_money) as payment_money  from  shop_order_group where create_time>{$startTime} and create_time <{$endTime}  and (status !=2 or status != 0) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
            $money = $table->querySql($sql);

            $sql = "select count(distinct ip) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
            $user = $table->querySql($sql);

            $sql = "select count(distinct ip) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
            $longin_user = $table->querySql($sql);

            $sql = "select count(*) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0  and merchant_id = " . yii::$app->session['uid'] . ";";
            $order = $table->querySql($sql);
            $data['turnover'][] = $money[0]['payment_money'] == null ? 0 : $money[0]['payment_money'];
            $data['visitor'] [] = $user[0]['num'] + $longin_user[0]['num'];
            $data['visit'][] = $order[0]['num'];
        }
        return $data;*/
    }

    public function yesterday($key){
        $data = array();
        $table = new TableModel();
        date_default_timezone_set('PRC');
        //当前时间整点数，+1实时统计
        $h = (int) date('H')+1;
        for ($i = 1; $i <= $h; $i++) {
            $data['h'][] = $i;
        }
        $time = date('Y-m-d H:00:00');
        date("Y-m-d",strtotime("-1 day"));
        //查询结果为当前整点到下一个小时的数据，下面循环会做处理，如当前9：20，结果中9的订单金额合计是9：00-9：20的合计
        $sql = "select sum(payment_money) as payment_money,FROM_UNIXTIME( create_time, '%k' ) AS data_time  from  shop_order_group where create_time > UNIX_TIMESTAMP( date_sub( '{$time}', INTERVAL {$h} HOUR ) )  and (status !=2 or status != 0) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . " GROUP BY data_time";
        $money = $table->querySql($sql);

        $sql = "select count(*) as visit_num,count(distinct ip) as num,FROM_UNIXTIME( create_time, '%k' ) AS data_time from  system_log where create_time > UNIX_TIMESTAMP( date_sub( '{$time}', INTERVAL {$h} HOUR ) )  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . " GROUP BY data_time;";
        $user = $table->querySql($sql);

        $data['turnover'] = [];
        foreach ($data['h'] as $key=>$val){
            $data['visitor'][$key] = 0;
            $data['visit'][$key] = 0;
            foreach ($money as $mk=>$mv){
                if ($mv['data_time'] == $val){
                    $data['turnover'][$key+1] = $mv['payment_money'];
                }
            }
            foreach ($user as $uk=>$uv){
                if ($uv['data_time'] == $val){
                    $data['visitor'][$key] = $uv['num'] + $uv['num'];
                    $data['visit'][$key] = $uv['visit_num'];
                }
            }
            if (!array_key_exists($key,$data['turnover'])){
                $data['turnover'][$key] = 0;
            }
        }
        //重新排序
        ksort($data['turnover']);
        return $data;
    }

    public function total_day($key) {
        $data = array();
        $table = new TableModel();
        date_default_timezone_set('PRC');
        //当前时间整点数，+1实时统计
        $h = (int) date('H')+1;
        for ($i = 1; $i <= $h; $i++) {
            $data['h'][] = $i;
        }
        $time = date('Y-m-d H:00:00');
        //查询结果为当前整点到下一个小时的数据，下面循环会做处理，如当前9：20，结果中9的订单金额合计是9：00-9：20的合计
        $sql = "select sum(payment_money) as payment_money,FROM_UNIXTIME( create_time, '%k' ) AS data_time  from  shop_order_group where create_time > UNIX_TIMESTAMP( date_sub( '{$time}', INTERVAL {$h} HOUR ) )  and (status !=2 or status != 0) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . " GROUP BY data_time";
        $money = $table->querySql($sql);

        $sql = "select count(*) as visit_num,count(distinct ip) as num,FROM_UNIXTIME( create_time, '%k' ) AS data_time from  system_log where create_time > UNIX_TIMESTAMP( date_sub( '{$time}', INTERVAL {$h} HOUR ) )  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . " GROUP BY data_time;";
        $user = $table->querySql($sql);

        $data['turnover'] = [];
        foreach ($data['h'] as $key=>$val){
            $data['visitor'][$key] = 0;
            $data['visit'][$key] = 0;
            foreach ($money as $mk=>$mv){
                if ($mv['data_time'] == $val){
                    $data['turnover'][$key+1] = $mv['payment_money'];
                }
            }
            foreach ($user as $uk=>$uv){
                if ($uv['data_time'] == $val){
                    $data['visitor'][$key] = $uv['num'] + $uv['num'];
                    $data['visit'][$key] = $uv['visit_num'];
                }
            }
            if (!array_key_exists($key,$data['turnover'])){
                $data['turnover'][$key] = 0;
            }
        }
        //重新排序
        ksort($data['turnover']);
        return $data;

        //原方法逻辑
        /*for ($i = 1; $i <= $h; $i++) {
            $a = $i - 1;
            $startTime = $a < 10 ? strtotime(date("Y-m-d") . " 0{$a}:00:00") : strtotime(date("Y-m-d") . " {$a}:00:00");
            $endTime = $i < 10 ? strtotime(date("Y-m-d") . " 0{$i}:00:00") : strtotime(date("Y-m-d") . " {$i}:00:00");
            $sql = "select sum(payment_money) as payment_money  from  shop_order_group where create_time>{$startTime} and create_time <{$endTime}  and (status !=2 or status != 0) and `key`='" . $key . "' and merchant_id = " . yii::$app->session['uid'] . ";";
            $money = $table->querySql($sql);

            $sql = "select count(distinct ip) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
            $user = $table->querySql($sql);

            $sql = "select count(distinct ip) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0 and user_id!=0 and merchant_id = " . yii::$app->session['uid'] . ";";
            $longin_user = $table->querySql($sql);

            $sql = "select count(*) as num  from  system_log where create_time>{$startTime} and create_time <{$endTime}  and `key`='" . $key . "' and sub_id = 0  and merchant_id = " . yii::$app->session['uid'] . ";";
            $order = $table->querySql($sql);
            $data['turnover'][] = $money[0]['payment_money'] == null ? 0 : $money[0]['payment_money'];
            $data['visitor'][] = $user[0]['num'] + $longin_user[0]['num'];
            $data['visit'][] = $order[0]['num'];
            $data['h'][] = $i;
        }

        return $data;*/
    }

    public function qcode($key) {

        try{
            $config = $this->getSystemConfig($key, "miniprogram");
            unset($config['wx_pay_type']);
            if ($config==false||count($config) == 0) {
                return "";
            }
            $pic = getConfig($config['app_id']);
            if ($pic == false||$pic=="") {
                $miniProgram = Factory::miniProgram($config);
                $response = $miniProgram->app_code->getUnlimit($key, ['width' => 280, "path" => '/pages/index/index/index']);
                $url = "";
                if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

                    $filename = $response->saveAs(yii::getAlias('@webroot/') . "/uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/", $config['app_id'] . ".png");
                    $localRes = "./uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/" . $filename;
                    setConfig($config['app_id'], $localRes);
                }
                return $localRes;
            } else {
                return $pic;
            }
        }catch (\EasyWeChat\Kernel\Exceptions\Exception $exception){
            return "";
        }

    }

}
