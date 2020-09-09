<?php

namespace app\controllers\shop;

use app\models\core\TableModel;
use app\models\shop\GoodsModel;
use app\models\shop\SubOrderModel;
use yii;
use yii\web\ShopController;
use yii\db\Exception;


/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TestController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['single', 'clear', 'update', 'test', 'vendor', 'test1'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionSingle()
    {
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/redis/order'); // 团长佣金
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/merchant/shop/goods/auto-obtained'); // 商品自动下架
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/goods'); //自动收货
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/order-close');//订单未付款 关闭
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/presale'); //预售订阅消息提醒
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/merchantGoodsMessage'); //预约商品到货通知
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/SolitaireGoods'); //接龙活动到期，清除活动商品接龙ID
        // curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/del-order'); //删除已取消订单
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/merchantAutoprint'); //易联云打印
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/supplierAutoprint'); //门店易联云打印
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/shop/order/group-order-process');//开团脚本
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/distribution');//计算分销各级佣金
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/SubscribeMessage'); // 订阅消息
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/group-un-order'); // 订阅消息
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/bargain'); // 订阅消息
        curlGet('https://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/shop/voucher/overdue');//优惠券到期自动失效

//        $bool = getConfig(date('Y-m-d'));
//        if ($bool) {
//            $day = date('d');
//            if ($day == "01") {
//                $his = date('His');
//                if ($his == '013000') {
////                    curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/StockRight');//计算分销运营商股权佣金  每月, 1日 1点30分执行(不计算股权佣金了)
//                }
//            }
//        }
    }

    public function actionClear()
    {
        if (yii::$app->request->isGet) {
            reidsAll();
            return result(200, '请求成功');
        }
    }

    public function actionUpdate()
    {
        $dbPrefix = "";
        $sqlfile = '../../update.sql';
        $dbStr = file_get_contents($sqlfile);
        /**
         * 执行SQL语句
         */
        $sqlFormat1 = $this->sql_split($dbStr, $dbPrefix, '');
        $counts = count($sqlFormat1);
        for ($i = 0; $i < $counts; $i++) {
            try {
                Yii::$app->db->createCommand($sqlFormat1[$i])->execute();
            } catch (Exception $e) {

            }

        }
        return result(200, '更新成功');
    }


    /**
     * 数据库语句解析
     * @param $sql 数据库
     * @param $newTablePre 新的前缀
     * @param $oldTablePre 旧的前缀
     */
    function sql_split($sql, $newTablePre, $oldTablePre)
    {
        //前缀替换
        if ($newTablePre != $oldTablePre) {
            $sql = str_replace($oldTablePre, $newTablePre, $sql);
        }
        $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $k => $query) {
            $ret[$k] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $ret[$k] .= $query;
            }
        }
        return $ret;
    }

    public function actionTest()
    {
        //$this->step1();
        // $this->step2();
        //  $this->step3();
    }

    public function step1($leader_uid)
    {
        $table = new TableModel();
        $sql = "update  shop_user set balance = 0 where id = {$leader_uid}";
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "delete from shop_user_balance where type =1 and type= 6 where user_id = {$leader_uid}";
        Yii::$app->db->createCommand($sql)->execute();
        //状态 0=待付款 1=待发货 2=已取消(24小时未支付) 3=已发货 4=已退款 5=退款中 6=待评价 7=已完成(评价后)  8=已删除  9一键退款  11=拼团中
        $sql = "select * from shop_order_group where  status =1 or status = 3 or status= 6 or status= 7  where leader_uid = {$leader_uid}";
        $groupOrders = $table->querySql($sql);

        $configModel = new \app\models\tuan\ConfigModel();
        $con = $configModel->do_one(['merchant_id' => 13, 'key' => 'ccvWPn']);
        for ($i = 0; $i < count($groupOrders); $i++) {
            $orderRs['data'] = $groupOrders[$i];

            $this->balance($groupOrders[$i]['order_sn'], $con['data']['commission_leader_ratio'], $con['data']['commission_selfleader_ratio']);
            $orderRs['data'] = $groupOrders[$i];
            if ($orderRs['data']['express_type'] == 2 && $orderRs['data']['express_price'] > 0 && $orderRs['data']['supplier_id'] == 0) {
                $balanceModel = new \app\models\shop\BalanceModel;
                $data['order_sn'] = $orderRs['data']['order_sn'];
                $data['key'] = $orderRs['data']['key'];
                $data['merchant_id'] = $orderRs['data']['merchant_id'];
                $data['money'] = $orderRs['data']['express_price'];
                $data['type'] = 6;
                $data['uid'] = $orderRs['data']['leader_uid'];
                $data['content'] = "配送费佣金";
                $balanceModel->do_add($data);
            }

            $balance = $this->balance($orderRs['data']['order_sn'], $con['data']['commission_leader_ratio'], 0);
            $data = array(
                'uid' => $orderRs['data']['leader_uid'],
                'order_sn' => $orderRs['data']['order_sn'],
                'money' => $balance[0],
                'content' => "团员消费",
                'type' => 1,
                'status' => 0
            );
            $data['key'] = $orderRs['data']['key'];
            $data['merchant_id'] = $orderRs['data']['merchant_id'];
            $balanceModel = new \app\models\shop\BalanceModel;
            $balanceModel->do_add($data);

            $sql = "update shop_order_group set  leader_money = {$balance[0]} where order_sn = {$orderRs['data']['order_sn']} ";
            Yii::$app->db->createCommand($sql)->execute();
        }
    }

    public function step2()
    {
        $table = new TableModel();
        $sql = "select * from shop_order_group where status =6 or status =7 ";
        $res = $table->querySql($sql);

        for ($i = 0; $i < count($res); $i++) {
            $sql = "select * from shop_user_balance where order_sn = '{$res[$i]['order_sn']}' and status = 0";
            $balance = $table->querySql($sql);
            for ($j = 0; $j < count($balance); $j++) {
                $sql = "update shop_user  set balance =balance+{$balance[$j]['money']} where id = {$balance[$j]['uid']};";
                Yii::$app->db->createCommand($sql)->execute();
            }
            $sql = "update shop_user_balance  set status =1 where order_sn = '{$res[$i]['order_sn']}' ";
            Yii::$app->db->createCommand($sql)->execute();
        }
    }


    public function step3($leader_uid)
    {
        $table = new TableModel();
        $sql = "select * from shop_user_balance where type= 2  and status  = 1 where user_id ={$leader_uid}";
        $res = $table->querySql($sql);

        for ($i = 0; $i < count($res); $i++) {
            $sql = "update shop_user set balance = balance-{$res[$i]['money']} where id = {$res[$i]['user_id']}";
            $res = $table->querySql($sql);
        }
    }

    public function balance($order_sn, $commission_leader_ratio, $commission_selfleader_ratio)
    {
        $money[0] = 0;
        $money[1] = 0;
        //根据订单查询子订单
        $orderSubModel = new SubOrderModel();
        $order = $orderSubModel->findall(['order_group_sn' => $order_sn]);
        //循环订单
        $goodsModel = new GoodsModel();
        for ($k = 0; $k < count($order['data']); $k++) {
            // $good = array(); //循环查询商品
            $good = $goodsModel->find(['id' => $order['data'][$k]['goods_id']]);
            // 判断商品是否单独设置佣金
            if ($good['data']['commission_leader_ratio'] != 0) {
                $money[0] = $money[0] + ($order['data'][$k]['payment_money'] * $good['data']['commission_leader_ratio'] / 100);
            } else {
                $money[0] = $money[0] + ($order['data'][$k]['payment_money'] * $commission_leader_ratio / 100);
            }
            if ($good['data']['commission_selfleader_ratio'] != 0) {
                $money[1] = $money[1] + ($order['data'][$k]['payment_money'] * $good['data']['commission_selfleader_ratio'] / 100);
            } else {
                $money[1] = $money[1] + ($order['data'][$k]['payment_money'] * $commission_selfleader_ratio / 100);
            }
        }

        return $money;
    }

    public function actionVendor()
    {
        {
            $url = "http://shouquanjs.juanpao.com/vendor.zip";
            $upgrade_file = $this->get_file($url);

            if (!$upgrade_file) {
                exit('下载升级文件失败!');
            }
            $this->unzip_file($upgrade_file, '../');

        }
    }

    function unzip_file($zipName, $dest)
    {
        if (!is_file($zipName)) {
            return false;
        }
        if (!is_dir($dest)) {
            mkdir($dest, 0777, true);
        }
        $zip = new \ZipArchive();
        if ($zip->open($zipName)) {
            $zip->extractTo($dest);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    function curl_get($url, $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_4 like Mac OS X)AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12H143 MicroMessenger/6.3.9)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $res = curl_exec($ch);
        if (!$res) {
            $res = curl_exec($ch);
        }
        curl_close($ch);
        return $res;
    }

    function get_file($url, $folder = '../data/')
    {
        set_time_limit(24 * 60 * 60);
        $target_dir = $folder . '';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $newfname = date('Ymd') . rand(1000, 10000000) . uniqid() . '.zip';
        $newfname = $target_dir . $newfname;
        $file = fopen($url, "rb");
        if ($file) {
            $newf = fopen($newfname, "wb");
            if ($newf) while (!feof($file)) {
                $buf = fread($file, 1024 * 8);
                if (strpos($buf, '{"status":0') === 0) {
                    $data = json_decode($buf, true);
                    exit($data['msg']);
                }
                fwrite($newf, $buf, 1024 * 8);
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return $newfname;
    }


    public function actionTest1()
    {
        $token = "35_SCiLgDZLBGOAM9MnLBpnvbY35wOlzdLaKqU_qsPg3MWsCHzNeRrS_Fou8V5CvV6yBkTEKwCe5s6nAwJw4syB950kk8IQ02uJxSdQXwGWSQgc6_accCp7XvXYeXwB0icjM6hy6TFbsL3hBfOHYDXhAFADIC";
        $filepath = 'http://ceshi.juanpao.cn/api/web/./uploads/merchant/shop/goods_picture/13/ccvWPn/15958375245f1e8c54e57f5.jpeg';
        $filepath = Yii::getAlias('@webroot/') . 'uploads/shop/13/ccvWPn/5_5ac473c81bd0f0.jpg';
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type=image";
        $data['media'] = "@" . $filepath;
        if(is_array($data) == true)
        {
            // Check each post field
            foreach($data as $key => $value)
            {
                // Convert values for keys starting with '@' prefix
                if(strpos($value, '@') === 0)
                {
                    // Get the file name
                    $filename = ltrim($value, '@');
                    // Convert the value to the new class
                    $data[$key] = new \CURLFile($filename);
                }
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output, true);
        var_dump($res);
        var_dump(123);
        die();

    }


}
