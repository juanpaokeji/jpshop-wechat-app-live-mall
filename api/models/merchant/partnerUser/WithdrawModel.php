<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\merchant\partnerUser;

use app\models\common\CommonModel;
use app\models\shop\OrderModel;
use app\models\system\SystemSubAdminBalanceModel;
use Yii;

/**
 * Class WithdrawModel
 * @package app\models\partnerUser
 */
class WithdrawModel extends CommonModel {

    /**
     * partner_withdraw
     * @return string
     */
    public static function tableName() {
        return 'partner_withdraw';
    }

    /**
     * 列表
     * @param $params
     * @return array
     */
    public function do_select($params) {
        //数据库操作
        $params['orderby'] = "id desc";
        $params['count'] = true;
        $res = $this->get_list($params);
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return ['status'=> 200, 'message' => '请求成功','data' => $res['data'], 'count' => $res['count']];
        }
    }
    /**
     * 新增
     * @param $params
     * @return array
     */
    public function add($params) {
        $data = array();
        $res = $this->modify($params, $data);
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return result(200, '新增成功',$res);
        }
    }


    /**
     * @param $where
     * @param $data
     * @return array
     */
    public function do_update($where, $data) {

        $res = $this->modify($where, $data);
        if ($res == false) {
            return result(500, '更新失败');
        } else {
           return result(200, '请求成功');
        }
    }


    /**
     * 查询单条数据
     * @param $params
     * @return array
     */
    public function one($params) {

        $res = $this->get_info($params);

        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return result(200, '请求成功',$res);
        }
    }

    /**
     * 删除
     * @param $where
     * @return array
     */
    public function do_delete($where) {
        $res = $this->soft_delete($where);
        if ($res == false) {
            return result(500, '删除失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 高德
     * @param $locations
     * @param int $type 0 是经纬度转地理位置 1是地理位置转经纬度
     * @return bool|string
     */
    public function getAddrGD($locations,$type = 0){
        if($type){
            $url = "https://restapi.amap.com/v3/geocode/regeo?key=6508c123d3c4d9d785ee82ac7ce37a81&location=".$locations;
            $result = json_decode($this->curl_get_contents($url),true);
            if($result['status'] == 1){
                return $result['regeocode']['addressComponent']['adcode'];
            }
        }else{
            $url = "https://restapi.amap.com/v3/geocode/geo?key=6508c123d3c4d9d785ee82ac7ce37a81&address=".$locations;
            $result = json_decode($this->curl_get_contents($url),true);
            if($result['status'] == 1){
                return $result['geocodes'][0]['adcode'];
            }
        }
        return false;
    }

    /**
     * 腾讯
     * @param $locations
     * @param int $type 0 是经纬度转地理位置 1是地理位置转经纬度
     * @return bool|string
     */
    public function getAddrTX($locations,$type = 0){
        if($type){
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?key=JMIBZ-JU2WK-OJJJB-AC7HK-4J4OZ-K6BTO&location=".$locations;
            $result = $this->curl_get_contents($url);
            if($result['status'] == 0){
                return $result['ad_info']['adcode'];
            }
        }else{
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?key=JMIBZ-JU2WK-OJJJB-AC7HK-4J4OZ-K6BTO&address=".$locations;
            $result = $this->curl_get_contents($url);
            if($result['status'] == 0){
                return ['ad_info']['adcode'];
            }
        }
        return false;
    }

    protected function curl_get_contents($url, $timeout = 5)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 0);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * 获取提现金额
     * @param $key
     * @param $merchant_id
     * @param $partner_id
     * @return bool|string
     * @throws \yii\db\Exception
     */
    public function getWithdrawMoney($key,$merchant_id,$partner_id){
        if (empty($key) || empty($merchant_id) || empty($partner_id)) {
            return false;
        }
        //查询合伙人订单
        $orderModel = new OrderModel();
        $where['merchant_id'] = $merchant_id;
        $where['`key`'] = $key;
        $where['partner_id'] = $partner_id;
        $where['status'] = 7;
        $where['is_partner_withdraw'] = 0;
        $where['limit'] = 10000;
        $total_money = 0;
        $brokerage = 0;
        $ids = '';
        $list = $orderModel->findList($where);
        if($list['status'] == 200){
            foreach ($list['data'] as $order_index=>$val){
                $total_money += $val['payment_money'];
                $ids .= $val['id'].',';
                if($val['supplier_id']){ //去计算system_sub_admin_balance表的佣金
                    $systemSubAdminBalanceModel = new SystemSubAdminBalanceModel();
                    $wheres['order_sn'] = $val['order_sn'];
                    $wheres['in'] = ['type', [1, 2, 3, 4, 5]];
                    $subBalanceLists = $systemSubAdminBalanceModel->do_select($wheres);
                    if($subBalanceLists['status'] == 200){
                        foreach ($subBalanceLists['data'] as $sub_val){
                            $brokerage += $sub_val['money'];
                        }
                    }
                }else{//去计算 shop_user_balance表的佣金
                    $shopUserBalanceModel = new \app\models\shop\BalanceModel();
                    $where_['order_sn'] = $val['order_sn'];
                    $where_['in'] = ['type', [1, 2, 3, 4, 5 ,6]];
                    $shopBalaceLists = $shopUserBalanceModel->do_select($where_);
                    if($shopBalaceLists['status'] == 200){
                        foreach ($shopBalaceLists['data'] as $shop_val){
                            $brokerage += $shop_val['money'];
                        }
                    }
                }
            }
        }
        $data['price'] =  bcsub($total_money,$brokerage,2);
        $data['ids'] = $ids;
        return $data;
    }
}
