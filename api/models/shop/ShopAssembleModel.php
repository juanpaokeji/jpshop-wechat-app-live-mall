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

use app\models\common\CommonModel;

/**
 * Class ShopAssembleModel
 * @package app\models\vip
 */
class ShopAssembleModel extends CommonModel {

    /**
     * shop_assemble
     * @return string
     */
    public static function tableName() {
        return 'shop_assemble';
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
            return ['status' => 200,'data' => $res['data'], 'count' => $res['count']];
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
            return result(204, '新增失败');
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
            return ['status' => 204, 'message' => '查询失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res];
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
     * 根据规格和人数查询商品拼团价格
     * @param $is_leader_discount
     * @param $jsonData
     * @param $where
     * @return bool
     */
    public static function searchGroupPrice($jsonData,$where,$is_leader_discount = 0){
        if(empty($jsonData) || empty($where)){
            return false;
        }
        $property = json_decode($jsonData,true);
        if(empty($property)){
            return false;
        }
        if(!isset($where['number']) || !isset($where['property1_name']) || !isset($where['property2_name'])){
            return false;
        }
        foreach ($property as $key=>$val){
            foreach ($val as $v){
                if($key == (int)$where['number'] && ($where['property1_name'] == $v['property1_name']) && ($where['property2_name'] == $v['property2_name'])){
                    if($is_leader_discount == 1){
                        $price = bcmul($v['price'],(100-$v['tuan_price'])/100,2);
                        return  $price == 0 ? 0.01: $price;
                    }else{
                        return $v['price'] == 0 ? 0.01: $v['price'];
                    }
                }
            }
        }
        return false;
    }
}
