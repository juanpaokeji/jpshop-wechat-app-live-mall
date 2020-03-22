<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\merchant\pay\PayModel;
use app\models\shop\OrderModel;
use app\models\shop\ShopAssembleAccessModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\UserModel;
use app\models\shop\VipAccessModel;
use app\models\system\SystemWxConfigModel;
use EasyWeChat\Factory;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use tools\pay\Payx;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @since 2.0
 */
class RabbitController extends Controller
{

    /**
     * 生产者
     * @throws \Exception
     */
    public function actionIndex()
    {
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('delay_exchange', 'direct', false, false, false);
        $channel->exchange_declare('cache_exchange', 'direct', false, false, false);

        $tale = new AMQPTable();
        $tale->set('x-dead-letter-exchange', 'delay_exchange');
        $tale->set('x-dead-letter-routing-key', 'delay_exchange');
        $tale->set('x-message-ttl', 86400000);

        $channel->queue_declare('cache_queue', false, true, false, false, false, $tale);
        $channel->queue_bind('cache_queue', 'cache_exchange', 'cache_exchange');

        $channel->queue_declare('delay_queue', false, true, false, false, false);
        $channel->queue_bind('delay_queue', 'delay_exchange', 'delay_exchange');


        $msg = new AMQPMessage('1', array(
            'expiration' => intval(0),
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT

        ));

        $channel->basic_publish($msg, 'cache_exchange', 'cache_exchange');
        echo date('Y-m-d H:i:s') . " [x] Sent 'Hello World!' " . PHP_EOL;

        $channel->close();
        $connection->close();
    }

    /**
     * 拼团订单消费 及时队列
     * @throws \ErrorException
     */
    public function actionCoustomer()
    {
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('delay_exchange0', 'direct', false, false, false);
        $channel->exchange_declare('cache_exchange0', 'direct', false, false, false);


        $channel->queue_declare('delay_queue0', false, true, false, false, false);
        $channel->queue_bind('delay_queue0', 'delay_exchange0', 'delay_exchange0');

        $callback = function ($msg) {
            try {
                echo date('Y-m-d H:i:s').'-----'.$msg->body. PHP_EOL. PHP_EOL;
                $order_sn = (string)$msg->body; // 订单码 处理拼团订单信息,开始处理业务
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '订单号：' . $order_sn . PHP_EOL, FILE_APPEND);
                if (!$order_sn) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                //检测订单是否支付
                $order_sql = "select * from shop_order_group where `order_sn`='{$order_sn}' and `status`=11";
                $orderInfo = yii::$app->db->createCommand($order_sql)->queryOne();
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '订单信息：' . json_encode($orderInfo) . PHP_EOL, FILE_APPEND);
                if (!$orderInfo) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                $groupOrderModel = new ShopAssembleAccessModel(); //检测是否有此拼团记录
                $groupOrderInfo = $groupOrderModel->one(['order_sn' => $order_sn, 'status' => 1]);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '拼团订单信息：' . json_encode($groupOrderInfo) . PHP_EOL, FILE_APPEND);
                if ($groupOrderInfo['status'] != 200) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                //判断当前这条订单是否是团长的
                if ($groupOrderInfo['data']['leader_id'] == 0 && $groupOrderInfo['data']['is_leader'] == 1) { // 是团长获取当前团是几人的团
                    $number = $groupOrderInfo['data']['number'];
                    $leader_id = $groupOrderInfo['data']['id'];
                    $expire_time = $groupOrderInfo['data']['expire_time'];
                    $leader_order_sn = $groupOrderInfo['data']['order_sn'];
                } else { //查找开团人的订单
                    $leaderOrderInfo = $groupOrderInfo = $groupOrderModel->one(['id' => $groupOrderInfo['data']['leader_id'], 'status' => 1]);
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '拼团人订单信息：' . json_encode($leaderOrderInfo) . PHP_EOL, FILE_APPEND);

                    if ($leaderOrderInfo['status'] != 200) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    }
                    $order_sql1 = "select * from shop_order_group where `order_sn`='{$leaderOrderInfo['data']['order_sn']}' and `status`=11";// 判断此开团人的订单是否是拼团中
                    $orderInfos = yii::$app->db->createCommand($order_sql1)->queryOne();
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '判断此开团人的订单是否是拼团中：' . json_encode($orderInfos) . PHP_EOL, FILE_APPEND);
                    if (empty($orderInfos)) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                        return  true;
                    }
                    $number = $leaderOrderInfo['data']['number'];
                    $leader_id = $leaderOrderInfo['data']['leader_id'];
                    $expire_time = $leaderOrderInfo['data']['expire_time'];
                    $leader_order_sn = $leaderOrderInfo['data']['order_sn'];
                }
                //查看拼团配置
                $groupConfigModel = new ShopAssembleModel();
                $configInfo = $groupConfigModel->one(['status' => 1, 'goods_id' => $groupOrderInfo['data']['goods_id'], 'key' => $groupOrderInfo['data']['key']]);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '拼团配置：' . json_encode($configInfo) . PHP_EOL, FILE_APPEND);

                if ($configInfo['status'] != 200) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                $is_automatic = $configInfo['data']['is_automatic'];
                //查找当前团已经有多少人参加了（且订单状态是11)
                $sql = "SELECT saa.`order_sn` FROM shop_assemble_access as saa
                            LEFT JOIN shop_order_group as sog ON saa.`order_sn` = sog.`order_sn` WHERE  saa.`status` = 1 and saa.`leader_id` = '{$leader_id}' and saa.`is_leader` = 0 and sog.`status` = 11";
                $order_sn_list = yii::$app->db->createCommand($sql)->queryAll();
                $group_number = count($order_sn_list) + 1;
                $temp_array = [];
                if (!empty($order_sn_list)) {
                    foreach ($order_sn_list as $val) {
                        $val = join(",", $val);
                        $temp_array[] = $val;
                    }
                    $temp_array[] = $leader_order_sn;
                } else {
                    $temp_array[] = $order_sn;
                }
                $str_order_sn = implode(",", $temp_array);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '订单order_sn：' . $str_order_sn . PHP_EOL, FILE_APPEND);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '拼团人数：' . $number . PHP_EOL, FILE_APPEND);
                if ($number <= $group_number) { //拼成功了 修改订单状态
                    $sql2 = "UPDATE shop_order_group SET `status` = 1 where `order_sn` in ({$str_order_sn}) and `status`=11";
                    yii::$app->db->createCommand($sql2)->execute();
                } else { //如果不等于，查看过期时间是否已到，时间到了则拼团失败，未开启，则此团失败，已开启，则成功
                    $now_time = time();
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '过期时间：' . $expire_time . PHP_EOL, FILE_APPEND);
                    if ($expire_time <= $now_time) { // 则关闭
                        $sql3 = "UPDATE shop_order_group SET `status` = 2 where `order_sn` in ({$str_order_sn}) and `status`=11";
                        $up_res_ = yii::$app->db->createCommand($sql3)->execute();
                        if (!$up_res_) {
                            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                            return  true;
                        }
                        // 执行退款
                        $new_order_sn = explode(',',$str_order_sn);
                        foreach ($new_order_sn as $order_sn) {
                            $sql4 = "select * from shop_order_group where `order_sn`='{$order_sn}'";
                            $orderInfo_ = yii::$app->db->createCommand($sql4)->queryOne();
                            if (empty($orderInfo_)) {
                                continue;
                            }
                            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '执行退款的订单：' . $order_sn . PHP_EOL, FILE_APPEND);

                            $res = self::RefundMoney($order_sn, $orderInfo_['key'], $orderInfo_['merchant_id']);
                            if ($res['result_code'] != "SUCCESS") {
                                //记录日志
                                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '退款失败的订单：' . $order_sn . PHP_EOL, FILE_APPEND);

                                $sqlx = "UPDATE shop_order_group SET `status` = 4,`refund` = 'pintuan',`after_sale` = 1 where `order_sn` in ({$order_sn})";
                                yii::$app->db->createCommand($sqlx)->execute();
                                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '修改订单状态为4：' . $order_sn . PHP_EOL, FILE_APPEND);

                                $balanceModel = new \app\models\shop\BalanceAccessModel();
                                $balanceModel->do_update(['order_sn' => $order_sn], ['status' => 2]);
                                continue;
                            }
                        }
                    } else {
                        $time_diff = ($expire_time - $now_time);
                        if ($time_diff <= 120 && $is_automatic == 1) { //开启虚拟成团了
                            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '开启虚拟成团了：' . $str_order_sn . PHP_EOL, FILE_APPEND);

                            $sql5 = "UPDATE shop_order_group SET `status` = 1 where `order_sn` in ({$str_order_sn}) and `status`=11";
                            yii::$app->db->createCommand($sql5)->execute();
                        }
                    }
                }
            } catch (\Exception $e) {
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', date('Y-m-d H:i:s') . '异常信息：' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log0.text', '---------------------------------------'. PHP_EOL. PHP_EOL. PHP_EOL, FILE_APPEND);
        };

        //只有consumer已经处理并确认了上一条message时queue才分派新的message给它
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('delay_queue0', '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }

    /**
     * 拼团订单消费 延时队列1
     * @throws \ErrorException
     */
    public function actionCoustomer1()
    {
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('delay_exchange300000', 'direct', false, false, false);
        $channel->exchange_declare('cache_exchange300000', 'direct', false, false, false);


        $channel->queue_declare('delay_queue300000', false, true, false, false, false);
        $channel->queue_bind('delay_queue300000', 'delay_exchange300000', 'delay_exchange300000');

        $callback = function ($msg) {
            try {
                echo date('Y-m-d H:i:s').'-----'.$msg->body. PHP_EOL. PHP_EOL;
                $order_sn = $msg->body; // 订单码 处理拼团订单信息,开始处理业务
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '订单号：' . $order_sn . PHP_EOL, FILE_APPEND);
                if (!$order_sn) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                //检测订单是否支付
                $order_sql = "select * from shop_order_group where `order_sn`='{$order_sn}' and `status`=11";
                $orderInfo = yii::$app->db->createCommand($order_sql)->queryOne();
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '订单信息：' . json_encode($orderInfo) . PHP_EOL, FILE_APPEND);
                if (!$orderInfo) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                $groupOrderModel = new ShopAssembleAccessModel(); //检测是否有此拼团记录
                $groupOrderInfo = $groupOrderModel->one(['order_sn' => $order_sn, 'status' => 1]);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '拼团订单信息：' . json_encode($groupOrderInfo) . PHP_EOL, FILE_APPEND);
                if ($groupOrderInfo['status'] != 200) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                //判断当前这条订单是否是团长的
                if ($groupOrderInfo['data']['leader_id'] == 0 && $groupOrderInfo['data']['is_leader'] == 1) { // 是团长获取当前团是几人的团
                    $number = $groupOrderInfo['data']['number'];
                    $leader_id = $groupOrderInfo['data']['id'];
                    $expire_time = $groupOrderInfo['data']['expire_time'];
                    $leader_order_sn = $groupOrderInfo['data']['order_sn'];
                } else { //查找开团人的订单
                    $leaderOrderInfo = $groupOrderInfo = $groupOrderModel->one(['id' => $groupOrderInfo['data']['leader_id'], 'status' => 1]);
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '拼团人订单信息：' . json_encode($leaderOrderInfo) . PHP_EOL, FILE_APPEND);

                    if ($leaderOrderInfo['status'] != 200) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    }
                    $order_sql1 = "select * from shop_order_group where `order_sn`='{$leaderOrderInfo['data']['order_sn']}' and `status`=11";// 判断此开团人的订单是否是拼团中
                    $orderInfos = yii::$app->db->createCommand($order_sql1)->queryOne();
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '判断此开团人的订单是否是拼团中：' . json_encode($orderInfos) . PHP_EOL, FILE_APPEND);
                    if (empty($orderInfos)) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                        return  true;
                    }
                    $number = $leaderOrderInfo['data']['number'];
                    $leader_id = $leaderOrderInfo['data']['leader_id'];
                    $expire_time = $leaderOrderInfo['data']['expire_time'];
                    $leader_order_sn = $leaderOrderInfo['data']['order_sn'];
                }
                //查看拼团配置
                $groupConfigModel = new ShopAssembleModel();
                $configInfo = $groupConfigModel->one(['status' => 1, 'goods_id' => $groupOrderInfo['data']['goods_id'], 'key' => $groupOrderInfo['data']['key']]);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '拼团配置：' . json_encode($configInfo) . PHP_EOL, FILE_APPEND);

                if ($configInfo['status'] != 200) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                $is_automatic = $configInfo['data']['is_automatic'];
                //查找当前团已经有多少人参加了（且订单状态是11)
                $sql = "SELECT saa.order_sn FROM shop_assemble_access as saa
                            LEFT JOIN shop_order_group as sog ON saa.order_sn = sog.order_sn WHERE  saa.status = 1 and saa.`leader_id` = {$leader_id} and saa.`is_leader` = 0 and sog.`status` = 11";
                $order_sn_list = yii::$app->db->createCommand($sql)->queryAll();
                $group_number = count($order_sn_list) + 1;
                $temp_array = [];
                if (!empty($order_sn_list)) {
                    foreach ($order_sn_list as $val) {
                        $val = join(",", $val);
                        $temp_array[] = $val;
                    }
                    $temp_array[] = $leader_order_sn;
                } else {
                    $temp_array[] = $order_sn;
                }
                $str_order_sn = implode(",", $temp_array);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '订单order_sn：' . $str_order_sn . PHP_EOL, FILE_APPEND);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '拼团人数：' . $number . PHP_EOL, FILE_APPEND);
                if ($number <= $group_number) { //拼成功了 修改订单状态
                    $sql2 = "UPDATE shop_order_group SET `status` = 1 where `order_sn` in ({$str_order_sn}) and `status`=11";
                    yii::$app->db->createCommand($sql2)->execute();
                } else { //如果不等于，查看过期时间是否已到，时间到了则拼团失败，未开启，则此团失败，已开启，则成功
                    $now_time = time();
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '过期时间：' . $expire_time . PHP_EOL, FILE_APPEND);
                    if ($expire_time <= $now_time) { // 则关闭
                        $sql3 = "UPDATE shop_order_group SET `status` = 2 where `order_sn` in ({$str_order_sn}) and `status`=11";
                        $up_res_ = yii::$app->db->createCommand($sql3)->execute();
                        if (!$up_res_) {
                            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                            return  true;
                        }
                        // 执行退款
                        $new_order_sn = explode(',',$str_order_sn);
                        foreach ($new_order_sn as $order_sn) {
                            $sql4 = "select * from shop_order_group where `order_sn`='{$order_sn}'";
                            $orderInfo_ = yii::$app->db->createCommand($sql4)->queryOne();
                            if (empty($orderInfo_)) {
                                continue;
                            }
                            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '执行退款的订单：' . $order_sn . PHP_EOL, FILE_APPEND);

                            $res = self::RefundMoney($order_sn, $orderInfo_['key'], $orderInfo_['merchant_id']);
                            if ($res['result_code'] != "SUCCESS") {
                                //记录日志
                                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '退款失败的订单：' . $order_sn . PHP_EOL, FILE_APPEND);

                                $sqlx = "UPDATE shop_order_group SET `status` = 4,`refund` = 'pintuan',`after_sale` = 1 where `order_sn` in ({$order_sn})";
                                yii::$app->db->createCommand($sqlx)->execute();
                                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '修改订单状态为4：' . $order_sn . PHP_EOL, FILE_APPEND);

                                $balanceModel = new \app\models\shop\BalanceAccessModel();
                                $balanceModel->do_update(['order_sn' => $order_sn], ['status' => 2]);
                                continue;
                            }
                        }
                    } else {
                        $time_diff = ($expire_time - $now_time);
                        if ($time_diff <= 120 && $is_automatic == 1) { //开启虚拟成团了
                            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '开启虚拟成团了：' . $str_order_sn . PHP_EOL, FILE_APPEND);

                            $sql5 = "UPDATE shop_order_group SET `status` = 1 where `order_sn` in ({$str_order_sn}) and `status`=11";
                            yii::$app->db->createCommand($sql5)->execute();
                        }
                    }
                }
            } catch (\Exception $e) {
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', date('Y-m-d H:i:s') . '异常信息：' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log1.text', '---------------------------------------'. PHP_EOL. PHP_EOL. PHP_EOL, FILE_APPEND);
        };

        //只有consumer已经处理并确认了上一条message时queue才分派新的message给它
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('delay_queue300000', '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }

    /**
     * 拼团订单消费 延时队列1
     * @throws \ErrorException
     */
    public function actionCoustomer2()
    {
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('delay_exchange600000', 'direct', false, false, false);
        $channel->exchange_declare('cache_exchange600000', 'direct', false, false, false);


        $channel->queue_declare('delay_queue600000', false, true, false, false, false);
        $channel->queue_bind('delay_queue600000', 'delay_exchange600000', 'delay_exchange600000');

        $callback = function ($msg) {
            try {
                echo date('Y-m-d H:i:s').'-----'.$msg->body. PHP_EOL. PHP_EOL;
                $order_sn = $msg->body; // 订单码 处理拼团订单信息,开始处理业务
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '订单号：' . $order_sn . PHP_EOL, FILE_APPEND);
                if (!$order_sn) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                //检测订单是否支付
                $order_sql = "select * from shop_order_group where `order_sn`='{$order_sn}' and `status`=11";
                $orderInfo = yii::$app->db->createCommand($order_sql)->queryOne();
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '订单信息：' . json_encode($orderInfo) . PHP_EOL, FILE_APPEND);
                if (!$orderInfo) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                $groupOrderModel = new ShopAssembleAccessModel(); //检测是否有此拼团记录
                $groupOrderInfo = $groupOrderModel->one(['order_sn' => $order_sn, 'status' => 1]);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '拼团订单信息：' . json_encode($groupOrderInfo) . PHP_EOL, FILE_APPEND);
                if ($groupOrderInfo['status'] != 200) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                //判断当前这条订单是否是团长的
                if ($groupOrderInfo['data']['leader_id'] == 0 && $groupOrderInfo['data']['is_leader'] == 1) { // 是团长获取当前团是几人的团
                    $number = $groupOrderInfo['data']['number'];
                    $leader_id = $groupOrderInfo['data']['id'];
                    $expire_time = $groupOrderInfo['data']['expire_time'];
                    $leader_order_sn = $groupOrderInfo['data']['order_sn'];
                } else { //查找开团人的订单
                    $leaderOrderInfo = $groupOrderInfo = $groupOrderModel->one(['id' => $groupOrderInfo['data']['leader_id'], 'status' => 1]);
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '拼团人订单信息：' . json_encode($leaderOrderInfo) . PHP_EOL, FILE_APPEND);

                    if ($leaderOrderInfo['status'] != 200) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    }
                    $order_sql1 = "select * from shop_order_group where `order_sn`='{$leaderOrderInfo['data']['order_sn']}' and `status`=11";// 判断此开团人的订单是否是拼团中
                    $orderInfos = yii::$app->db->createCommand($order_sql1)->queryOne();
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '判断此开团人的订单是否是拼团中：' . json_encode($orderInfos) . PHP_EOL, FILE_APPEND);
                    if (empty($orderInfos)) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                        return  true;
                    }
                    $number = $leaderOrderInfo['data']['number'];
                    $leader_id = $leaderOrderInfo['data']['leader_id'];
                    $expire_time = $leaderOrderInfo['data']['expire_time'];
                    $leader_order_sn = $leaderOrderInfo['data']['order_sn'];
                }
                //查看拼团配置
                $groupConfigModel = new ShopAssembleModel();
                $configInfo = $groupConfigModel->one(['status' => 1, 'goods_id' => $groupOrderInfo['data']['goods_id'], 'key' => $groupOrderInfo['data']['key']]);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '拼团配置：' . json_encode($configInfo) . PHP_EOL, FILE_APPEND);

                if ($configInfo['status'] != 200) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return  true;
                }
                $is_automatic = $configInfo['data']['is_automatic'];
                //查找当前团已经有多少人参加了（且订单状态是11)
                $sql = "SELECT saa.order_sn FROM shop_assemble_access as saa
                            LEFT JOIN shop_order_group as sog ON saa.order_sn = sog.order_sn WHERE  saa.status = 1 and saa.`leader_id` = {$leader_id} and saa.`is_leader` = 0 and sog.`status` = 11";
                $order_sn_list = yii::$app->db->createCommand($sql)->queryAll();
                $group_number = count($order_sn_list) + 1;
                $temp_array = [];
                if (!empty($order_sn_list)) {
                    foreach ($order_sn_list as $val) {
                        $val = join(",", $val);
                        $temp_array[] = $val;
                    }
                    $temp_array[] = $leader_order_sn;
                } else {
                    $temp_array[] = $order_sn;
                }
                $str_order_sn = implode(",", $temp_array);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '订单order_sn：' . $str_order_sn . PHP_EOL, FILE_APPEND);
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '拼团人数：' . $number . PHP_EOL, FILE_APPEND);
                if ($number <= $group_number) { //拼成功了 修改订单状态
                    $sql2 = "UPDATE shop_order_group SET `status` = 1 where `order_sn` in ({$str_order_sn}) and `status`=11";
                    yii::$app->db->createCommand($sql2)->execute();
                } else { //如果不等于，查看过期时间是否已到，时间到了则拼团失败，未开启，则此团失败，已开启，则成功
                    $now_time = time();
                    file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '过期时间：' . $expire_time . PHP_EOL, FILE_APPEND);
                    if ($expire_time <= $now_time) { // 则关闭
                        $sql3 = "UPDATE shop_order_group SET `status` = 2 where `order_sn` in ({$str_order_sn}) and `status`=11";
                        $up_res_ = yii::$app->db->createCommand($sql3)->execute();
                        if (!$up_res_) {
                            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                            return  true;
                        }
                        // 执行退款
                        $new_order_sn = explode(',',$str_order_sn);
                        foreach ($new_order_sn as $order_sn) {
                            $sql4 = "select * from shop_order_group where `order_sn`='{$order_sn}'";
                            $orderInfo_ = yii::$app->db->createCommand($sql4)->queryOne();
                            if (empty($orderInfo_)) {
                                continue;
                            }
                            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '执行退款的订单：' . $order_sn . PHP_EOL, FILE_APPEND);

                            $res = self::RefundMoney($order_sn, $orderInfo_['key'], $orderInfo_['merchant_id']);
                            if ($res['result_code'] != "SUCCESS") {
                                //记录日志
                                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '退款失败的订单：' . $order_sn . PHP_EOL, FILE_APPEND);

                                $sqlx = "UPDATE shop_order_group SET `status` = 4,`refund` = 'pintuan',`after_sale` = 1 where `order_sn` in ({$order_sn})";
                                yii::$app->db->createCommand($sqlx)->execute();
                                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '修改订单状态为4：' . $order_sn . PHP_EOL, FILE_APPEND);

                                $balanceModel = new \app\models\shop\BalanceAccessModel();
                                $balanceModel->do_update(['order_sn' => $order_sn], ['status' => 2]);
                                continue;
                            }
                        }
                    } else {
                        $time_diff = ($expire_time - $now_time);
                        if ($time_diff <= 120 && $is_automatic == 1) { //开启虚拟成团了
                            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '开启虚拟成团了：' . $str_order_sn . PHP_EOL, FILE_APPEND);

                            $sql5 = "UPDATE shop_order_group SET `status` = 1 where `order_sn` in ({$str_order_sn}) and `status`=11";
                            yii::$app->db->createCommand($sql5)->execute();
                        }
                    }
                }
            } catch (\Exception $e) {
                file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', date('Y-m-d H:i:s') . '异常信息：' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log2.text', '---------------------------------------'. PHP_EOL. PHP_EOL. PHP_EOL, FILE_APPEND);
        };

        //只有consumer已经处理并确认了上一条message时queue才分派新的message给它
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('delay_queue600000', '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }

    /**
     * 退款
     * @param $order_sn
     * @param $key
     * @param $merchant_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function RefundMoney($order_sn, $key, $merchant_id)
    {
        $sql5 = "select * from shop_order_group where `order_sn` = '{$order_sn}' and `merchant_id`={$merchant_id} and `key`='{$key}'";
        $orderData = yii::$app->db->createCommand($sql5)->queryOne();
        $sqlpay = "select * from system_pay where order_id = {$order_sn}";
        $pays = yii::$app->db->createCommand($sqlpay)->queryOne();
        //获取商户微信配置
        if ($orderData['order_type'] == 1) {
            $config = self::getSystemConfig($key, "wxpay", 1, $merchant_id);
            $config['notify_url'] = "https://api.juanpao.com/pay/wechat/notifyreturn";
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $app = Factory::payment($config);
            // 参数分别为：微信订单号、商户退款单号、订单金额、退款金额、其他参数
            $res = $app->refund->byTransactionId($pays['transaction_id'], $order_sn, 1, 1, ['refund_desc' => '商品退款', 'notify_url' => 'https://api.juanpao.com/pay/wechat/notifyreturn']);
        } elseif ($orderData['order_type'] == 3) { //余额退款
            $sql5 = "select * from shop_user where id = {$orderData['user_id']}";
            $userInfo = yii::$app->db->createCommand($sql5)->queryOne();
            if ($userInfo) {
                $data['recharge_balance'] = bcadd($orderData['payment_money'], $userInfo['recharge_balance'], 2);
                $sql5 = "UPDATE shop_user SET `recharge_balance` = {$data['recharge_balance']} where id = {$orderData['user_id']} and `key`='{$key}'";
                $re_ = yii::$app->db->createCommand($sql5)->execute();
                if ($re_) {
                    $res = ['result_code' => 'SUCCESS', 'result_msg' => 'yue'];
                } else {
                    $res = ['result_code' => 'FAIL'];
                }
            }
        } else {
            $config = self::getSystemConfig($key, "miniprogrampay", 1, $merchant_id);
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            if ($config['wx_pay_type'] == 1) {
                $config['notify_url'] = "https://api.juanpao.com/pay/wechat/notifyreturn";
                $app = Factory::payment($config);
                // 参数分别为：微信订单号、商户退款单号、订单金额、退款金额、其他参数
                $res = $app->refund->byTransactionId($pays['transaction_id'], $order_sn, $orderData['payment_money'] * 100, $orderData['payment_money'] * 100, ['refund_desc' => '商品退款']);
            } else {
                $mini_pay = new \tools\pay\refund\Refund();
                $mini_pay->setPay_ver(Payx::PAY_VER);
                $mini_pay->setPay_type("010");
                $mini_pay->setService_id(Payx::SERVICE_ID);
                $mini_pay->setMerchant_no($config['merchant_no']);
                $mini_pay->setTerminal_id($config['terminal_id']);
                $mini_pay->setTerminal_trace($order_sn);
                $mini_pay->setTerminal_time(date("YmdHis"));
                $mini_pay->setRefund_fee($orderData['data']['payment_money'] * 100);
                $mini_pay->setOut_trade_no($pays['transaction_id']);
                $pay_pre = Payx::refund($mini_pay, $config['saobei_access_token']);
                if ($pay_pre->return_code == "01" && $pay_pre->result_code == '01') {
                    $sql6 = "UPDATE shop_order_group SET `after_sale` = 1,`refund` = 'saobei',`status` = 4 where order_sn = {$order_sn} and `key`='{$key}'";
                    yii::$app->db->createCommand($sql6)->execute();
                    $balanceModel = new \app\models\shop\BalanceAccessModel();
                    $balanceModel->do_update(['order_sn' => $order_sn], ['status' => 2]);
                    $res = ['result_code' => 'SUCCESS', 'result_msg' => 'saobei'];
                } else {
                    $res = ['result_code' => 'FAIL'];
                }
            }
        }
        //修改当前订单的优惠卷状态改成0
        $sql6 = "UPDATE shop_voucher SET `status` = 0 where order_sn = {$order_sn} and `key`='{$key}'";
        yii::$app->db->createCommand($sql6)->execute();
        return $res;
    }

    public static function getSystemConfig($key, $type, $is_pay = 0, $merchant_id)
    {
        $config = getConfig($key);
        if ($is_pay == 1) {
            $sql5 = "select * from merchant_user where id = {$merchant_id}";
            $res = yii::$app->db->createCommand($sql5)->queryOne();
            if ($res) {
                if ($res['pay_switch'] == 0 && $config['wx_pay_type'] == 1) {
                    return false;
                }
            }
        }
        if ($config == false) {
            $sql5 = "select * from system_wx_config where `key` = '{$key}'";
            $systemConfig = yii::$app->db->createCommand($sql5)->queryOne();
            if ($systemConfig) {
                if ($systemConfig['wx_pay_type'] == 1) {
                    $array['miniprogrampay'] = $systemConfig['miniprogram_pay'];
                } else {
                    $array['miniprogrampay'] = $systemConfig['saobei'];
                }
                $array['wechat'] = $systemConfig['wechat'];
                $array['wxpay'] = $systemConfig['wechat_pay'];
                $array['miniprogram'] = $systemConfig['miniprogram'];
                $array['wx_pay_type'] = $systemConfig['wx_pay_type'];
                setConfig($key, $array);
                $arr = self::config($array, $type);
                $arr['wx_pay_type'] = $systemConfig['wx_pay_type'];
                return $arr;
            } else {
                return false;
            }
        } else {
            $arr = self::config($config, $type);
            $arr['wx_pay_type'] = isset($config['wx_pay_type']) ? $config['wx_pay_type'] : 1;
            return $arr;
        }
    }

    public static function config($array, $type)
    {
        if (!isset($array[$type])) {
            return false;
        }
        if ($array == false) {
            return false;
        } else {
            return json_decode($array[$type], true);
        }
    }

    public function actionTest()
    {
        $order_sn = '201908071837138872';//$msg->body; // 订单码 处理拼团订单信息,开始处理业务
        $order_sql = "select * from shop_order_group where `order_sn`='{$order_sn}' and `status`=11";
        try{
            $orderInfo = yii::$app->db->createCommand($order_sql)->queryOne();
            yii::$app->db->close();
            file_put_contents(dirname(dirname(__FILE__)) . '/web' . '/rabbit_log3.text', date('Y-m-d H:i:s') . '信息：' .json_encode($orderInfo) . PHP_EOL, FILE_APPEND);

        }catch (\Exception $e){
            echo $e->getMessage();
        }

    }
}
