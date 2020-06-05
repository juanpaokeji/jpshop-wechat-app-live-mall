<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\shop;

//引入各表实体
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use yii;
use app\models\core\TableModel;
use yii\db\Exception;

/**
 *
 * @version   2018年04月16日
 * @author    YangJing <120912212@qq.com>
 * @copyright Copyright 2018 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class OrderModel extends TableModel
{

    public $table = "shop_order_group";
    public $tableSummary = "shop_order";
    public $tableVoucher = "shop_voucher";

    public function queryOrder($params)
    {
        //数据库操作
        try {
            $table = new TableModel();
            $params['delete_time is null'] = null;
            $params['table'] = 'shop_order_group';

            $res = $table->tableList($params);
            $app = $res['app'];
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    /**
     * 查询 订单概述需要的数据
     * @param $params
     * @return array
     */
    public function findSummary($params)
    {
        //数据库操作
        try {
            $table = new TableModel();
            $params['delete_time is null'] = null;
            $params['table'] = 'shop_order_group';

            $res = $table->tableList($params);
            $app = $res['app'];
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    /**
     * 查询主订单列表接口
     * @param $params
     * @return array
     */
//    public function findAll($params) {
//        //数据库操作
////        $table = new TableModel();
////        $params['shop_order_group.delete_time is null'] = null;
////        // $params['fields'] = "shop_order_group.*";
////        $params['table'] = $this->table;
////        $params['join'] = " inner join shop_order as so ON so.order_group_sn = shop_order_group.order_sn " .
////                " inner join shop_user as su ON su.id = shop_order_group.user_id ";
////        $params['orderby'] = " shop_order_group.id desc";
//        try {
//            $table = new TableModel();
//            $params['shop_order_group.delete_time is null'] = null;
//            $params['fields'] = "shop_order_group.*,su.nickname as su_nickname,IFNULL(sv.price,'0') as sv_price";
//            $params['table'] = $this->table;
//            $params['join'] = ' left join shop_user su on su.id = shop_order_group.user_id ' .
//                    " left join shop_voucher sv on sv.id = shop_order_group.voucher_id " .
//                    " left join shop_user_contact suc on suc.id = shop_order_group.user_contact_id ";
//            $params['orderby'] = " shop_order_group.id desc";
//            if (isset($params['goods_name'])) {
//                if ($params['goods_name'] != "") {
//                    $params['goods_name'] = trim($params['goods_name']);
//                    $params["sg.name like '%{$params['goods_name']}%'"] = null;
//                }
//                unset($params['goods_name']);
//            }
//            if (isset($params['user_id'])) {
//                if ($params['user_id'] != "") {
//                    $params['user_id'] = trim($params['user_id']);
//                    $params["shop_order_group.user_id"] = $params['user_id'];
//                }
//                unset($params['user_id']);
//            }
//            if (isset($params['start_time'])) {
//                if ($params['start_time'] != "") {
//                    $time = strtotime(str_replace("+", " ", $params['start_time']));
//                    $params["shop_order_group.create_time >={$time} "] = null;
//                }
//                unset($params['start_time']);
//            }
//            if (isset($params['end_time'])) {
//                if ($params['end_time'] != "") {
//                    $time = strtotime(str_replace("+", " ", $params['end_time']));
//                    $params["shop_order_group.create_time <={$time} "] = null;
//                }
//                unset($params['end_time']);
//            }
//            if (isset($params['searchNameType'])) {
//                if ($params['searchNameType'] != "") {
//                    if ($params['searchName'] != "") {
//                        if ($params['searchNameType'] == 1) {
//                            $params['shop_order_group.order_sn'] = trim($params['searchName']);
//                        }
//                        if ($params['searchNameType'] == 2) {
//                            $name = trim($params['searchName']);
//                            $params["suc.name like '%{$name}%'"] = null;
//                        }
//                        if ($params['searchNameType'] == 3) {
//                            $params['suc.phone'] = trim($params['searchName']);
//                        }
//                    }
//                }
//                unset($params['searchNameType']);
//                unset($params['searchName']);
//            }
//            if (isset($params['status'])) {
//                if ($params['status'] != "") {
//                    if ($params['status'] == 2) {
//                        $params['shop_order_group.status = 2 or shop_order_group.status = 4 '] = null;
//                    } else if ($params['status'] == 6) {
//                        $params['shop_order_group.status = 6 or shop_order_group.status = 7 '] = null;
//                    } else {
//                        $params['shop_order_group.status'] = $params['status'];
//                    }
//                }
//                unset($params['status']);
//            }
////            if (isset($params['logistics_type'])) {
////                if ($params['logistics_type'] != "") {
////                    $params['sg.type'] = $params['logistics_type'];
////                }
////                unset($params['logistics_type']);
////            }
//            if (isset($params['pay_type'])) {
//                if ($params['pay_type'] != "") {
//                    $params['sp.type'] = $params['pay_type'];
//                }
//                unset($params['pay_type']);
//            }
//            if (isset($params['after_sale'])) {
//                if ($params['after_sale'] != "") {
//                    $params['shop_order_group.after_sale'] = $params['after_sale'];
//                }
//                unset($params['after_sale']);
//            }
//
//            $res = $table->tableList($params);
//            $app = $res['app'];
//        } catch (Exception $ex) {
//            return result(500, '数据库操作失败');
//        }
//        //返回数据 时间格式重置
//        for ($i = 0; $i < count($app); $i++) {
//            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
//            if ($app[$i]['update_time'] != "") {
//                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
//            }
//        }
//        if (empty($app)) {
//            return result(204, '查询失败');
//        } else {
//            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
//        }
//    }

    public function findAll($params)
    {

        $table = new TableModel();
        $params['shop_order_group.delete_time is null'] = null;
        $params['table'] = $this->table;
        $params['orderby'] = " shop_order_group.id desc";

        if (isset($params['goods_name'])) {
            if ($params['goods_name'] != "") {
                $params['goods_name'] = trim($params['goods_name']);
                $params["goodsname like '%{$params['goods_name']}%'"] = null;
            }
            unset($params['goods_name']);
        }
        if (isset($params['user_id'])) {
            if ($params['user_id'] != "") {
                $params['user_id'] = trim($params['user_id']);
                $params["shop_order_group.user_id"] = $params['user_id'];
            }
            unset($params['user_id']);
        }
        if (isset($params['order_sn'])) {
            if ($params['order_sn'] != "") {
                $params['order_sn'] = trim($params['order_sn']);
                $params["shop_order_group.order_sn"] = $params['order_sn'];
            }
            unset($params['order_sn']);
        }
        if (isset($params['start_time'])) {
            if ($params['start_time'] != "") {
                $time = strtotime(str_replace("+", " ", $params['start_time']));
                $params["shop_order_group.create_time >={$time} "] = null;
            }
            unset($params['start_time']);
        }
        if (isset($params['end_time'])) {
            if ($params['end_time'] != "") {
                $time = strtotime(str_replace("+", " ", $params['end_time']));
                $params["shop_order_group.create_time <={$time} "] = null;
            }
            unset($params['end_time']);
        }
        if (isset($params['searchNameType'])) {
            if ($params['searchNameType'] != "") {
                if ($params['searchName'] != "") {
                    if ($params['searchNameType'] == 1) {
                        $params['shop_order_group.order_sn'] = trim($params['searchName']);
                    }
                    if ($params['searchNameType'] == 2) {
                        $name = trim($params['searchName']);
                        $params["shop_order_group.name like '%{$name}%'"] = null;
                    }
                    if ($params['searchNameType'] == 3) {
                        $params['shop_order_group.phone'] = trim($params['searchName']);
                    }
                }
            }
            unset($params['searchNameType']);
            unset($params['searchName']);
        }
        if (isset($params['status'])) {
            if ($params['status'] != "") {
                if ($params['status'] == 2) {
                    $params['(shop_order_group.status = 2 or shop_order_group.status = 4) '] = null;
                } else if ($params['status'] == 6) {
                    $params['(shop_order_group.status = 6 or shop_order_group.status = 7 )'] = null;
                } else if ($params['status'] == 5) {
                    $params['after_sale <> -1 '] = null;
                } else {
                    $params['shop_order_group.status'] = $params['status'];
                }
            }
            unset($params['status']);
        }
//            if (isset($params['logistics_type'])) {
//                if ($params['logistics_type'] != "") {
//                    $params['sg.type'] = $params['logistics_type'];
//                }
//                unset($params['logistics_type']);
//            }
        if (isset($params['pay_type'])) {
            if ($params['pay_type'] != "") {
                $params['sp.type'] = $params['pay_type'];
            }
            unset($params['pay_type']);
        }
        if (isset($params['after_sale'])) {
            if ($params['after_sale'] != "") {
                $params['shop_order_group.after_sale'] = $params['after_sale'];
            }
            unset($params['after_sale']);
        }
        if (isset($params['leader_uid'])) {
            if ($params['leader_uid'] != "") {
                $params['shop_order_group.leader_uid'] = $params['leader_uid'];
            }
            unset($params['leader_uid']);
        }
        $data = $table->tableList($params);
        $orders = $data['app'];
        $params['fields'] = " shop_order_group.*,st.weight,system_express.name as express_name,shop_order_group.admin_remark as group_admin_remark,so.name as goodsname,so.pic_url as goods_url,so.goods_id,so.property1_name,so.property2_name,so.stock_id,so.number,so.total_price as order_total_price,so.price,so.confirm_time,so.send_out_time,so.payment_money as order_payment_money,so.express_id,so.express_number,so.admin_remark ,su.nickname,shop_voucher.price as voucher_price,shop_tuan_leader.area_name,province_code,city_code,area_code,shop_tuan_leader.addr,shop_tuan_leader.realname,shop_user.phone as leader_phone";
        $params['join'] = " inner join shop_order as so ON so.order_group_sn = shop_order_group.order_sn " .
            " left join shop_stock as st ON st.id = so.stock_id " .
            " left join shop_user as su ON su.id = shop_order_group.user_id " .
            " left join shop_voucher on shop_voucher.id=shop_order_group.voucher_id " .
            " left join shop_tuan_leader on shop_tuan_leader.uid=shop_order_group.leader_self_uid " .
            " left join shop_user on shop_tuan_leader.uid=shop_user.id " .
            " left join system_express on so.express_id=system_express.id ";
        unset($params['limit']);
        unset($params['page']);
        $params['shop_tuan_leader.supplier_id'] = 0;
        $res = $table->tableList($params);
        $app = $res['app'];
        $res = array();
        for ($j = 0; $j < count($orders); $j++) {
            for ($i = 0; $i < count($app); $i++) {
                if ($app[$i]['order_sn'] == $orders[$j]['order_sn']) {
                    $order = array();
                    $res[$j]['id'] = $app[$i]['id'];
                    $res[$j]['order_sn'] = $app[$i]['order_sn'];
                    $res[$j]['user_id'] = $app[$i]['user_id'];
                    $res[$j]['user_contact_id'] = $app[$i]['user_contact_id'];
                    $res[$j]['total_price'] = $app[$i]['total_price'];
                    $res[$j]['is_tuan'] = $app[$i]['is_tuan'];
                    $res[$j]['express_type'] = $app[$i]['express_type'];
                    $res[$j]['leader_uid'] = $app[$i]['leader_uid'];
                    $res[$j]['leader_uid'] = $app[$i]['leader_uid'];
                    $res[$j]['leader_self_uid'] = $app[$i]['leader_self_uid'];
                    $res[$j]['tuan_status'] = $app[$i]['tuan_status'];
                    $res[$j]['express_price'] = $app[$i]['express_price'];
                    $res[$j]['payment_money'] = $app[$i]['payment_money'];
                    $res[$j]['voucher_id'] = $app[$i]['voucher_id'];
                    $res[$j]['nickname'] = $app[$i]['nickname'];
                    $res[$j]['address'] = $app[$i]['address'];
                    $res[$j]['phone'] = $app[$i]['phone'];
                    $res[$j]['name'] = $app[$i]['name'];
                    $res[$j]['after_sale'] = $app[$i]['after_sale'];
                    $res[$j]['after_type'] = $app[$i]['after_type'];
                    $res[$j]['after_phone'] = $app[$i]['after_phone'];
                    $res[$j]['after_addr'] = $app[$i]['after_addr'];
                    $res[$j]['after_remark'] = $app[$i]['after_remark'];
                    $res[$j]['after_imgs'] = $app[$i]['after_imgs'];
                    $res[$j]['after_express_number'] = $app[$i]['after_express_number'];
                    $res[$j]['after_admin_imgs'] = $app[$i]['after_admin_imgs'];
                    $res[$j]['after_admin_remark'] = $app[$i]['after_admin_remark'];
                    $res[$j]['order_type'] = $app[$i]['order_type'];
                    $res[$j]['status'] = $app[$i]['status'];
                    $res[$j]['remark'] = $app[$i]['remark'] == null ? "" : $app[$i]['remark'];
                    $res[$j]['group_admin_remark'] = $app[$i]['group_admin_remark'] == null ? "" : $app[$i]['group_admin_remark'];
                    $res[$j]['refund'] = $app[$i]['refund'];
                    $res[$j]['create_time'] = $app[$i]['create_time'] == 0 ? "" : date('Y-m-d H:i:s', $app[$i]['create_time']);
                    $res[$j]['voucher_price'] = $app[$i]['voucher_price'] == null ? 0 : $app[$i]['voucher_price'];
                    $res[$j]['province_code'] = $app[$i]['province_code'];
                    $res[$j]['city_code'] = $app[$i]['city_code'];
                    $res[$j]['area_code'] = $app[$i]['area_code'];
                    $res[$j]['area_name'] = $app[$i]['area_name'];
                    $res[$j]['addr'] = $app[$i]['addr'];
                    $res[$j]['realname'] = $app[$i]['realname'];
                    $res[$j]['leader_phone'] = $app[$i]['leader_phone'];
                    $res[$j]['express_name'] = $app[$i]['express_name'];
                    $res[$j]['express_number'] = $app[$i]['express_number'];
                    $res[$j]['is_assemble'] = $app[$i]['is_assemble'];
                    $res[$j]['is_bargain'] = $app[$i]['is_bargain'];
                    $order['name'] = $app[$i]['goodsname'];
                    $order['pic_url'] = $app[$i]['goods_url'];
                    $order['goods_id'] = $app[$i]['goods_id'];
                    $order['property1_name'] = $app[$i]['property1_name'];
                    $order['property2_name'] = $app[$i]['property2_name'];
                    $order['stock_id'] = $app[$i]['stock_id'];
                    $order['weight'] = $app[$i]['weight'];
                    $order['number'] = $app[$i]['number'];
                    $order['total_price'] = $app[$i]['order_total_price'];
                    $order['price'] = $app[$i]['price'];
                    $order['confirm_time'] = $app[$i]['confirm_time'] == 0 ? "" : date('Y-m-d H:i:s', $app[$i]['confirm_time']);
                    $order['send_out_time'] = $app[$i]['send_out_time'] == 0 ? "" : date('Y-m-d H:i:s', $app[$i]['send_out_time']);
                    $order['payment_money'] = $app[$i]['order_payment_money'];
                    $order['express_id'] = $app[$i]['express_id'];
                    // $order['remark'] = $app[$i]['remark'];
                    $order['admin_remark'] = $app[$i]['admin_remark'];
                    $res[$j]['order'][] = $order;
                }
            }
        }
        try {
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res, 'count' => $data['count']];
        }
    }

    public function shop_order($params)
    {
        $table = new TableModel();
        $params['shop_order_group.delete_time is null'] = null;
        $params['fields'] = "shop_order_group.* ";
        $params['orderby'] = " shop_order_group.id desc ";
        $params['table'] = $this->table;
        if (isset($params['status'])) {
            if ($params['status'] == 8) {
                $params[' (shop_order_group.status = 0 or shop_order_group.status = 1 or shop_order_group.status = 2 or shop_order_group.status = 3 or shop_order_group.status = 6 or shop_order_group.status = 7)'] = null;
                unset($params['status']);
            } else {
                $params['shop_order_group.status'] = $params['status'];
                unset($params['status']);
            }
        }

        try {
            $res = $table->tableList($params);

            $app = $res['app'];
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
            }
            $data['table'] = "shop_order";

            $data['order_group_sn'] = $app[$i]['order_sn'];
            $rs = $table->tableList($data);

            $numbers = 0;
            for ($j = 0; $j < count($rs['app']); $j++) {
                $numbers = $numbers + $rs['app'][$j]['number'];
            }
            $app[$i]['order'] = $rs['app'];
            $app[$i]['numbers'] = $numbers;
        }

        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    /**
     * 查询子订单列表接口
     * @param $params
     * @return array
     */
    public function findSuborder($params)
    {
        //数据库操作

            $table = new TableModel();
            $params['shop_order.delete_time is null'] = null;
            $params['fields'] = " shop_order.* ";
            $params['table'] = 'shop_order';
            $res = $table->tableList($params);
            $app = $res['app'];
        try {} catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
            }
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    public function findList($params)
    {
        //数据库操作

        $table = new TableModel();
        try {
            $params['delete_time is null'] = null;
            $params['table'] = $this->table;
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["name like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }
            $res = $table->tableList($params);
            $app = $res['app'];
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
            }
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    /**
     * 查询单条接口
     * @param $params
     * @return array
     */
//    public function one($params) {
//
//        $table = new TableModel();
//        //数据库操作
//        if (isset($params['id'])) {
//            $where['id'] = $params['id'];
//        }
//        if (isset($params['`key`'])) {
//            $where['`key`'] = $params['`key`'];
//        }
//        if (isset($params['merchant_id'])) {
//            $where['merchant_id'] = $params['merchant_id'];
//        }
//        if (isset($params['user_id'])) {
//            $where['user_id'] = $params['user_id'];
//        }
//        $where['delete_time is null'] = null;
//
//        try {
//            $fields = " id,order_sn,user_contact_id,total_price,express_price,payment_money,create_time,voucher_id";
//            $app = $table->tableSingle($this->table, $where, $fields);
//        } catch (Exception $ex) {
//            return result(500, '数据库操作失败');
//        }
//        if (gettype($app) != 'array') {
//            return result(204, '查询失败');
//        } else {
//            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
//            $orderParams['table'] = 'shop_order';
//            $orderParams['order_group_sn'] = $app['order_sn'];
//            $orders = $table->tableList($orderParams);
//
//            for ($i = 0; $i < count($orders['app']); $i++) {
//                $goods = $table->tableSingle('shop_goods', ['id' => $orders['app'][$i]['goods_id']]);
//                $goods['nums'] = $orders['app'][$i]['number'];
//                $goods['stock'] = $table->tableSingle('shop_stock', ['id' => $orders['app'][$i]['stock_id']]);
//                $app['goods'][] = $goods;
//            }
//
//            $express = $table->tableSingle('shop_express', ['id' => $orders['app'][0]['express_id']]);
//            $company = $table->tableSingle('system_express', ['id' => $express['system_express_id']]);
//
//            $app['express'] = logistics($orders['app'][0]['express_number'], $company['simple_name']);
//            $address = $table->tableSingle('shop_user_comment', ['id' => $app['user_contact_id']]);
//            $app['address'] = $address;
//            $systempay = $table->tableSingle('system_pay', ['order_id' => $app['order_sn']]);
//            $app['transaction_id'] = $systempay['transaction_id'];
//            $app['pay_time'] = $systempay['pay_time'];
//            $app['send_out_time'] = $orders['app'][0]['send_out_time'];
//            $voucher = $table->tableSingle('shop_voucher', ['id' => $app['voucher_id']]);
//            $app['voucher'] = $voucher['price'];
//            return result(200, '请求成功', $app);
//        }
//    }
    public function one($params)
    {
        $table = new TableModel();
        //数据库操作
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
            unset($params['id']);
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
        }
        if (isset($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
            unset($params['user_id']);
        }
        if (isset($params['order_sn'])) {
            $where['order_sn'] = $params['order_sn'];
            unset($params['order_sn']);
        }
        $where['delete_time is null'] = null;
        $table = new TableModel();

        try {
            $params['delete_time is null'] = null;
            $params['fields'] = " shop_order_group.* ";
            $params['table'] = $this->table;
            $res = $table->tableSingle($this->table, $where);
            $app = $res;
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        if($app==false){
            return result(204, '查询失败');
        }
        $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
        if ($app['update_time'] != "") {
            $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
        }
        $data['table'] = "shop_order";
        $data['order_group_sn'] = $app['order_sn'];
        $rs = $table->tableList($data);
        $numbers = 0;
        for ($j = 0; $j < count($rs['app']); $j++) {
            $numbers = $numbers + $rs['app'][$j]['number'];
            $send_out_time = $rs['app'][$j]['send_out_time'];
        }
        $app['order'] = $rs['app'];
        $app['numbers'] = $numbers;

        if ($app['voucher_id'] == 0) {
            $voucher_price = ['price' => 0];
        } else {
            $voucher_price = $table->tableSingle("shop_voucher", ['id' => $app['voucher_id']]);
        }

        $app['voucher_price'] = $voucher_price;
        $app['send_out_time'] = $send_out_time == 0 ? "" : date('Y-m-d H:i:s', $send_out_time);
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app];
        }
    }

    /**
     * 查询单条接口
     * @param $params
     * @return array
     */
    public function find($params)
    {

        $table = new TableModel();
        //数据库操作
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
        }
        if (isset($params['order_sn'])) {
            $where['order_sn'] = $params['order_sn'];
        }
        if (isset($params['sid'])) {
            $where['supplier_id'] = $params['sid'];
        }

        if (isset($params['transaction_order_sn'])) {
            $where['transaction_order_sn'] = $params['transaction_order_sn'];
        }
        $where['delete_time is null'] = null;

        try {
            $app = $table->tableSingle($this->table, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
        } else {
            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }
            return result(200, '请求成功', $app);
        }
    }

    public function select($where)
    {

        $table = new TableModel();

        $where['delete_time is null'] = null;
        try {
            $app = $table->tableSingle($this->table, $where, '', ' id desc');
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
        } else {
            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }
            return result(200, '请求成功', $app);
        }
    }

    public function express($params)
    {
        $table = new TableModel();
        //数据库操作
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
            unset($params['id']);
        }
        if (isset($params['order_sn'])) {
            $where['order_sn'] = $params['order_sn'];
            unset($params['order_sn']);
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
        }
        $where['delete_time is null'] = null;
        try {
            $app = $table->tableSingle($this->table, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
        } else {
            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
            $orderParams['table'] = 'shop_order';
            $orderParams['order_group_sn'] = $app['order_sn'];
            $orders = $table->tableList($orderParams);
           // $express = $table->tableSingle('shop_express', ['id' => $orders['app'][0]['express_id']]);
            $company = $table->tableSingle('system_express', ['id' => $orders['app'][0]['express_id']]);

            $shopExpress = logistics($orders['app'][0]['express_number'], $company['simple_name']);
            $shopExpress['mailNo'] = $orders['app'][0]['express_number'];
            $shopExpress['expTextName'] = $company['name'];
            return result(200, '请求成功', $shopExpress);
        }
    }

    /**
     * 新增接口
     * 地址:/admin/group/add
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function add($params)
    {
        //data 新增数据参数设置
        //数据库操作
        $table = new TableModel();
        $params['create_time'] = time();
        $res = $table->tableAdd($this->table, $params);
        try {

        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '新增失败');
        } else {
            return result(200, '请求成功', $res);
        }
    }

    /**
     * 删除接口
     * 地 址:/admin/group/delete
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function delete($params)
    {
        //where条件设置
        $where = ['id' => $params['id']];
        //params 参数设置
        unset($params['id']);
        $params['delete_time'] = time();
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableUpdate($this->table, $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(204, '删除失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 更新主订单接口
     * @param $params
     * @return array
     */
    public function update($params)
    {
        //where 条件设置
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
            unset($params['id']);
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
        }
        if (isset($params['order_sn'])) {
            $where['order_sn'] = $params['order_sn'];
            unset($params['order_sn']);
        }
        if (isset($params['transaction_order_sn'])) {
            $where['transaction_order_sn'] = $params['transaction_order_sn'];
            unset($params['order_sn']);
        }
        $where['delete_time is null'] = null;
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作

        try {
            $table = new TableModel();
            $res = $table->tableUpdate($this->table, $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

    public function update1($params)
    {
        //where 条件设置
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
            unset($params['id']);
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
        }
        if (isset($params['order_sn'])) {
            $where['order_sn'] = $params['order_sn'];
            unset($params['order_sn']);
        }
        $where['delete_time is null'] = null;
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作

        try {
            $table = new TableModel();
            $res = $table->tableUpdate($this->table, $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 更新主订单号
     * @param $params
     * @return array
     */
    public function updateOrderSn($params)
    {
        //where 条件设置
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
            unset($params['id']);
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
        }

        $where['delete_time is null'] = null;
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        try {
            $table = new TableModel();
            $res = $table->tableUpdate($this->table, $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 更新子订单接口
     * @param $params
     * @return array
     */
    public function updateSuborder($params)
    {
        //where 条件设置
        $where['id'] = $params['id'];
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            $where['delete_time is null'] = null;
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            $where['delete_time is null'] = null;
        }
        unset($params['id']);
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        try {
            $table = new TableModel();
            $res = $table->tableUpdate($this->tableSummary, $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 发货更新 model
     * @param $params
     * @return array
     * @throws Exception
     */
    public function updateSend($params, $type = 1)
    {
        //where 条件设置
        $where['order_sn'] = $params['order_sn'];
        unset($params['order_sn']);
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
            $where['delete_time is null'] = null;
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
            $where['delete_time is null'] = null;
        }
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        //1.将主订单状态改为已发货 2.通过主订单id循环将快递信息保存到对应的子订单中
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $express_id = $params['express_id'];
            $express_number = $params['express_number'];
            unset($params['express_id']);
            unset($params['express_number']);
            $params['status'] = 3; //主订单需要修改的状态，改完后删除
            if ($type == 2) {
                $params['tuan_status'] = 1; //若是团购订单还得修改团购状态
            }
            $params['after_sale'] = -1; //主订单需要修改的退款状态，改完后删除
            $table = new TableModel();
            $table->tableUpdate($this->table, $params, $where);
            unset($params['send_express_type']);
            unset($params['supplier_id']);
            unset($params['status']);
            unset($params['after_sale']);
            unset($params['tuan_status']);
            $params['express_id'] = $express_id;
            $params['send_out_time'] = time();
            $params['express_number'] = $express_number;
            $where['order_group_sn'] = $where['order_sn'];
            unset($where['order_sn']);
            $table->tableUpdate($this->tableSummary, $params, $where);
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            return result(200, "发货成功");
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "发货失败");
        }
    }

    /**
     * 取消订单 model
     * @param $params
     * @return array
     * @throws Exception
     */
    public function cancel($params)
    {
        //where 条件设置
        $where['id'] = $params['id'];
        unset($params['order_sn']);
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
            $where['delete_time is null'] = null;
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
            $where['delete_time is null'] = null;
        }
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        //1.将主订单状态改为已取消 status = 2, 2.通过主订单id查询对应的优惠券，状态改为 is_used = 0
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $params['status'] = 2; //主订单需要修改的状态，改完后删除
            $table = new TableModel();
            $table->tableUpdate($this->table, $params, $where);
            unset($params['status']);
            //获取优惠券id
            $res = $table->tableSingle($this->table, $where, 'voucher_id');
            unset($params['id']);
            $where['id'] = $res['voucher_id'];
            $params['is_used'] = 0;
            $table->tableUpdate($this->tableVoucher, $params, $where);
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            return result(200, "取消订单成功");
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "取消订单失败");
        }
    }

    /**
     *  根据订单编号查询子订单 商品信息
     */
    public function goodsOrder($params)
    {
        $table = new TableModel();
        try {
            $sql = "select * from shop_order_group  inner join shop_order on shop_order.order_group_sn = shop_order_group.order_sn inner join shop_stock on shop_stock.id = shop_order.stock_id inner join shop_goods on shop_goods.id = shop_order.goods_id where shop_order_group.order_sn = {$params['order_sn']} and merchant_id = {$params['merchant_id']} and `key` = {$params['`key`']}";
            $res = $table->querySql($sql);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res];
        }
    }

    public function comment($params)
    {
        $table = new TableModel();
        try {
            $sql = " SELECT * FROM shop_order " .
                "INNER JOIN shop_goods ON shop_goods.id = shop_order.stock_id" .
                "INNER JOIN shop_user ON shop_user.id = shop_order.user_id" .
                "INNER JOIN shop_user_comment ON shop_user_comment.order_id = shop_order.id";
            $where = "shop_order.goods_id = {$params['goods_id']} and shop_order.`key` = {$params['`key`']} and shop_order.merchant_id = {$params['merchant_id']} ";
            $res = $table->querySql($sql . $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res];
        }
    }

    public function timeOutOrder()
    {
        $table = new TableModel();
        $sql = "update shop_order_group set status = 2 where id in ( SELECT t.id FROM (SELECT id FROM shop_order_group where timediff(now(), from_unixtime(create_time)) >'23:59:59' and status = 0) as t);";
        $res = yii::$app->db->createCommand($sql)->execute();
        return $res;
    }

    //电子面单 查询订单
    public function findAllPirnt($params)
    {

        $table = new TableModel();
        $params['shop_order_group.delete_time is null'] = null;
        $params['table'] = $this->table;
        $params['orderby'] = " shop_order_group.id desc";
        $params['groupby'] = " so.order_group_sn ";
        $params['join'] = " left join shop_order as so on so.order_group_sn= shop_order_group.order_sn left join system_pay on system_pay.order_id = shop_order_group.order_sn ";
        //time_type=1&time_type_value=2019-04-24 - 2019-04-25&status=1&type=2&type_value=123131313&print_type=2&is_print=0
        if (isset($params['time_type'])) {
            if (isset($params['time_type_value'])) {
                if ($params['time_type_value'] != "") {
                    $time = explode(" - ", $params['time_type_value']);
                    $start_time = strtotime($time[0]);
                    $end_time = strtotime($time[1]);
                    if ($params['time_type'] == 1) {
                        $params["shop_order_group.create_time >={$start_time}"] = null;
                        $params["shop_order_group.create_time <={$end_time}"] = null;
                    } else if ($params['time_type'] == 2) {
                        $params["system_pay.pay_time >={$start_time}"] = null;
                        $params["system_pay.pay_time <={$end_time}"] = null;
                    } else if ($params['time_type'] == 3) {
                        $params["shop_order_group.print_time >={$start_time}"] = null;
                        $params["shop_order_group.print_time <={$end_time}"] = null;
                    } else if ($params['time_type'] == 5) {
                        $params["so.send_out_time >={$start_time}"] = null;
                        $params["so.send_out_time <={$end_time}"] = null;
                    } else if ($params['time_type'] == 4) {
                        $params["shop_order_group.send_print_time >={$start_time}"] = null;
                        $params["shop_order_group.send_print_time <={$end_time}"] = null;
                    }
                }
            }
            unset($params['time_type_value']);
            unset($params['time_type']);
        }
        if (isset($params['status'])) {
            if ($params['status'] != "") {
                if ($params['status'] == 2) {
                    $params['(shop_order_group.status = 2 or shop_order_group.status = 4 )'] = null;
                } else if ($params['status'] == 6) {
                    $params['(shop_order_group.status = 6 or shop_order_group.status = 7 )'] = null;
                } else {
                    $params['shop_order_group.status'] = $params['status'];
                }
            }
            unset($params['status']);
        }
        if (isset($params['type'])) {
            if ($params['type'] == 1) {
                $params['goods_name'] = trim($params['type_value']);
                $params["shop_order_group.goodsname like '%{$params['goods_name']}%'"] = null;
                unset($params['goods_name']);
            } else if ($params['type'] == 2) {
                $params['shop_order_group.order_sn'] = trim($params['type_value']);
            } else if ($params['type'] == 3) {
                $name = trim($params['type_value']);
                $params["shop_order_group.name like '%{$name}%'"] = null;
            } else if ($params['type'] == 4) {
                $params['shop_order_group.phone'] = trim($params['type_value']);
            } else if ($params['type'] == 5) {
                $params['shop_order_group.leader_uid'] = trim($params['type_value']);
            }
            unset($params['type_value']);
            unset($params['type']);
        }
        if (isset($params['print_type'])) {
            if (isset($params['is_print'])) {
                if ($params['is_print'] == 1) {
                    $params['shop_order_group.is_sent_print'] = $params['is_print'];
                    unset($params['is_print']);
                } else if ($params['is_print'] == 2) {
                    $params['shop_order_group.is_print'] = $params['is_print'];
                    unset($params['is_print']);
                }
            }
            unset($params['is_print']);
            unset($params['print_type']);
        }

        $data = $table->tableList($params);
        $page = $params['page'];
        $limit = $params['limit'];
        unset($params['page']);
        unset($params['limit']);
        $number = $table->tableList($params);
        $userNumber = array();
        $order_sn = "";
        for ($i = 0; $i < count($number['app']); $i++) {
            $userNumber[] = $number['app'][$i]['user_id'];
            if($i==1){
                $order_sn = $number['app'][$i]['order_sn'];
            }else{
                $order_sn = $order_sn.",".$number['app'][$i]['order_sn'];
            }
        }
        $userNumber = array_unique($userNumber);
        $params['page'] = $page;
        $params['limit'] = $limit;
        try {
            $orders = $data['app'];
            $params['fields'] = " shop_order_group.*,shop_goods.property1 as goods_property1,shop_goods.property2 as goods_property2,system_pay.pay_time ,so.name as goodsname,so.goods_id,so.pic_url as goods_url,so.goods_id,so.property1_name,so.property2_name,so.stock_id,so.number,so.total_price as order_total_price,so.price,so.confirm_time,so.send_out_time,so.payment_money as order_payment_money,so.express_id,so.express_number,so.admin_remark ,su.nickname,shop_voucher.price as voucher_price ";
            $params['join'] = " inner join shop_order as so ON so.order_group_sn = shop_order_group.order_sn " .
                " left join shop_user as su ON su.id = shop_order_group.user_id " .
                " left join shop_voucher on shop_voucher.id=shop_order_group.voucher_id " .
                " left join system_pay on system_pay.order_id=shop_order_group.order_sn " .
                " left join shop_goods on shop_goods.id=so.goods_id ";
            unset($params['limit']);
            unset($params['page']);
            unset($params['groupby']);
            $res = $table->tableList($params);
            $app = $res['app'];
            
            $res = array();

            for ($j = 0; $j < count($orders); $j++) {
                for ($i = 0; $i < count($app); $i++) {
                    if ($app[$i]['order_sn'] == $orders[$j]['order_sn']) {
                        $order = array();
                        $address = explode('-', $app[$i]['address']);
                        $res[$j]["province"] = "";
                        $res[$j]["city"] = "";
                        $res[$j]["area"] = "";
                        $res[$j]["addr"] = "";
                        $res[$j]["postcode"] = "";
                        if (is_array($address) && count($address) == 5) {
                            $res[$j]["province"] = $address[0];
                            $res[$j]["city"] = $address[1];
                            $res[$j]["area"] = $address[2];
                            $res[$j]["addr"] = $address[3];
                            $res[$j]["postcode"] = $address[4];
                        }
                        $res[$j]['id'] = $app[$i]['id'];
                        $res[$j]['is_tuan'] = $app[$i]['is_tuan'];
                        $res[$j]['order_sn'] = $app[$i]['order_sn'];
                        $res[$j]['user_id'] = $app[$i]['user_id'];
                        $res[$j]['express_type'] =$app[$i]['express_type'];
                        $res[$j]['is_tuan'] =$app[$i]['is_tuan'];
                        $res[$j]['user_contact_id'] = $app[$i]['user_contact_id'];
                        $res[$j]['total_price'] = $app[$i]['total_price'];
                        $res[$j]['express_price'] = $app[$i]['express_price'];
                        $res[$j]['payment_money'] = $app[$i]['payment_money'];
                        $res[$j]['voucher_id'] = $app[$i]['voucher_id'];
                        $res[$j]['nickname'] = $app[$i]['nickname'];
                        $res[$j]['address'] = $app[$i]['address'];
                        $res[$j]['phone'] = $app[$i]['phone'];
                        $res[$j]['name'] = $app[$i]['name'];
                        $res[$j]['leader_uid'] = $app[$i]['leader_uid'];
                        $res[$j]['after_sale'] = $app[$i]['after_sale'];
                        $res[$j]['after_type'] = $app[$i]['after_type'];
                        $res[$j]['after_phone'] = $app[$i]['after_phone'];
                        $res[$j]['after_addr'] = $app[$i]['after_addr'];
                        $res[$j]['after_remark'] = $app[$i]['after_remark'];
                        $res[$j]['after_imgs'] = $app[$i]['after_imgs'];
                        $res[$j]['after_express_number'] = $app[$i]['after_express_number'];
                        $res[$j]['after_admin_imgs'] = $app[$i]['after_admin_imgs'];
                        $res[$j]['after_admin_remark'] = $app[$i]['after_admin_remark'];
                        $res[$j]['order_type'] = $app[$i]['order_type'];
                        $res[$j]['status'] = $app[$i]['status'];
                        $res[$j]['remark'] = $app[$i]['remark'] == null ? "" : $app[$i]['remark'];
                        $res[$j]['refund'] = $app[$i]['refund'];
                        $res[$j]['create_time'] = $app[$i]['create_time'] == 0 ? "" : date('Y-m-d H:i:s', $app[$i]['create_time']);
                        $res[$j]['voucher_price'] = $app[$i]['voucher_price'] == null ? 0 : $app[$i]['voucher_price'];
                        $res[$j]['admin_remark'] = $app[$i]['admin_remark'] == null ? "" : $app[$i]['admin_remark'];
                        $order['name'] = $app[$i]['goodsname'];
                        $order['goods_id'] = $app[$i]['goods_id'];
                        $order['pic_url'] = $app[$i]['goods_url'];
                        $order['goods_id'] = $app[$i]['goods_id'];
                        $property1_name = explode(":", $app[$i]['goods_property1']);
                        $property2_name = explode(":", $app[$i]['goods_property2']);
                        if (is_array($property1_name)) {
                            $order['property1_name'] = $property1_name[0] . ":" . $app[$i]['property1_name'];
                        } else {
                            $order['property1_name'] = "";
                        }
                        if (is_array($property2_name)) {
                            $order['property2_name'] = $property2_name[0] . ":" . $app[$i]['property2_name'];
                        } else {
                            $order['property2_name'] = "";
                        }

                        $order['stock_id'] = $app[$i]['stock_id'];
                        $order['number'] = $app[$i]['number'];
                        $order['total_price'] = $app[$i]['order_total_price'];
                        $order['price'] = $app[$i]['price'];
                        $order['confirm_time'] = $app[$i]['confirm_time'] == 0 ? "" : date('Y-m-d H:i:s', $app[$i]['confirm_time']);
                        $order['send_out_time'] = $app[$i]['send_out_time'] == 0 ? "" : date('Y-m-d H:i:s', $app[$i]['send_out_time']);
                        $order['payment_money'] = $app[$i]['order_payment_money'];
                        $order['express_id'] = $app[$i]['express_id'];
                        $order['express_number'] = $app[$i]['express_number'];
                        // $order['remark'] = $app[$i]['remark'];
                        $order['admin_remark'] = $app[$i]['admin_remark'];
                        $res[$j]['order'][] = $order;
                    }
                }
            }
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res, 'count' => count($number['app']), 'user' => count($userNumber)];
        }
    }

    public function tuanOrder($params)
    {
        $params['is_tuan'] = $params['delete_time is null'] = null;
        $params['table'] = $this->table;
        $params['orderby'] = " id desc";
        $type = $params['type'];
        unset($params['type']);
        $table = new TableModel();
        if (isset($params['order_status'])) {

            if ($params['order_status'] == 1) {
                $params['status not in (2,4,8,9)'] = null;
            } else if ($params['order_status'] == 2) {
                $params['status'] = 0;
            } else if ($params['order_status'] == 3) {
                $params['status'] = 1;
            } else if ($params['order_status'] == 4) {
                $params['status'] = 3;
                $params['(express_type = 1 or express_type = 2)'] = null;
                $params['tuan_status'] = 1;
            } else if ($params['order_status'] == 5) {
                $params['status'] = 3;
                $params['(express_type = 1 or express_type = 2)'] = null;
                $params['tuan_status'] = 2;
            } else if ($params['order_status'] == 6) {
                $params['(status =7 or status= 6)'] = null;
            } else {
                unset($params['status']);
            }
            unset($params['order_status']);
        }

        if (isset($params['user_id'])) {
            $params['user_id'] = $params['user_id'];
        }
        if (isset($params['page'])) {
            $params['limit'] = 10;
        }
        try {
            $params['fields'] = "id,order_sn,name,phone,express_price  as express_balace,payment_money,address,tuan_status,express_type,status,user_id,leader_uid,leader_self_uid,create_time";
            if (isset($params['text'])) {
                $params["(phone like '%{$params['text']}%' or order_sn like '%{$params['text']}%' or goodsname like '%{$params['text']}%' or name like '%{$params['text']}%' or user_id = {$params['text']})"] = null;
                unset($params['text']);
            }

            $res = $table->tableList($params);

            $app = $res['app'];
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }

        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
            $data['fields'] = "id,name,property1_name,property2_name,payment_money,pic_url,number,total_price";
            $data['table'] = "shop_order";
            $data['order_group_sn'] = $app[$i]['order_sn'];
            $rs = $table->tableList($data);
            $app[$i]['stock'] = $rs['app'];

            $sql = "select id,avatar from shop_user where id = {$app[$i]['user_id']}";
            $avatar = $table->querySql($sql);
            $app[$i]['avatar'] = $avatar[0]['avatar'];
            $sql = "select sum(money)as number from shop_user_balance where order_sn = '{$app[$i]['order_sn']}' and  uid= {$app[$i]['leader_self_uid']} and  type = 1";

            $balace = $table->querySql($sql);
            $app[$i]['balace'] = $balace[0]['number'] == null ? 0 : $balace[0]['number'];

            if ($type == 2 && $app[$i]['express_type'] == 2) {
                $sql = "select sum(money)as number from shop_user_balance where order_sn = '{$app[$i]['order_sn']}' and uid={$app[$i]['leader_uid']} and type= 6";
                $balace = $table->querySql($sql);
                $app[$i]['express_balace'] = $balace[0]['number'] == null ? 0 : $balace[0]['number'];
            }
        }

        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    /**
     * 加如队列(生产者)
     * @param string $message 发送信息
     * @param int $expiration 过期时间 最大86400000  毫秒
     * @throws \Exception
     */
    public static function rabbitGroupOrder($message, $expiration)
    {
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('delay_exchange' . $expiration, 'direct', false, false, false);
        $channel->exchange_declare('cache_exchange' . $expiration, 'direct', false, false, false);

        $tale = new AMQPTable();
        $tale->set('x-dead-letter-exchange', 'delay_exchange' . $expiration);
        $tale->set('x-dead-letter-routing-key', 'delay_exchange' . $expiration);
        $tale->set('x-message-ttl', 86400000); //毫秒

        $channel->queue_declare('cache_queue' . $expiration, false, true, false, false, false, $tale);
        $channel->queue_bind('cache_queue' . $expiration, 'cache_exchange' . $expiration, 'cache_exchange' . $expiration);

        $channel->queue_declare('delay_queue' . $expiration, false, true, false, false, false);
        $channel->queue_bind('delay_queue' . $expiration, 'delay_exchange' . $expiration, 'delay_exchange' . $expiration);


        $msg = new AMQPMessage($message, array(
            'expiration' => intval($expiration),
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ));
        $channel->basic_publish($msg, 'cache_exchange' . $expiration, 'cache_exchange' . $expiration);
        file_put_contents(Yii::getAlias('@webroot/') . '/rabbit_message.text', date('Y-m-d H:i:s') . "sent message" . PHP_EOL, FILE_APPEND);
        $channel->close();
        $connection->close();
    }

}
