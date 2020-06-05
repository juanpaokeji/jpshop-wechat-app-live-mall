<?php

namespace app\controllers\shop;

use app\models\core\TableModel;
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
                'except' => ['single', 'clear', 'update', 'test'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionSingle()
    {

        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/merchant/shop/goods/auto-obtained'); // 商品自动下架
        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/SubscribeMessage'); // 订阅消息
        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/goods'); //自动收货
        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/order-close');//订单未付款 关闭
       // curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/task/task/del-order'); //删除已取消订单
        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/merchantAutoprint'); //易联云打印
        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/supplierAutoprint'); //门店易联云打印
        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/shop/order/group-order-process');//开团脚本
        curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/distribution');//计算分销各级佣金

        $bool = getConfig(date('Y-m-d'));
        if ($bool) {
            $day = date('d');
            if ($day == "01") {
                $his = date('His');
                if ($his == '013000') {
//                    curlGet('http://' . $_SERVER['HTTP_HOST'] . '/api/web/index.php/StockRight');//计算分销运营商股权佣金  每月, 1日 1点30分执行(不计算股权佣金了)
                }
            }
        }
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


    public function actionVendor(){
        {
            $url = "http://shouquanjs.juanpao.com/vendor.zip";
            $upgrade_file = $this->get_file($url);
            if(!$upgrade_file){
                exit('下载升级文件失败!');
            }
            $this->unzip_file($upgrade_file,'../');

        }
    }

    function unzip_file($zipName,$dest)
    {
        if (!is_file($zipName)) {
            return false;
        }
        if (!is_dir($dest)) {
            mkdir($dest, 0777, true);
        }
        $zip = new ZipArchive();
        if ($zip->open($zipName)) {
            $zip->extractTo($dest);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    function curl_get($url, $timeout = 10){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_4 like Mac OS X)AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12H143 MicroMessenger/6.3.9)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $res  =  curl_exec($ch);
        if(!$res){
            $res  =  curl_exec($ch);
        }
        curl_close($ch);
        return $res;
    }

}
