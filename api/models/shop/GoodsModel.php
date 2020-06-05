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
class GoodsModel extends TableModel
{

    public $table = "shop_goods";

    /**
     * 查询列表接口
     * 地址:/admin/group/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function findall($params)
    {
        //数据库操作
        $table = new TableModel();
        try {
            if (!isset($params['fields'])) {
                $params['fields'] = " *,(select IFNULL(sum(number),0) from shop_order where  shop_order.goods_id = shop_goods.id) as sold ";
            }

            if ($params['delete_time'] == 1) {
                $params['shop_goods.delete_time is null'] = null;
                unset($params['delete_time']);
            } else {
                $params['shop_goods.delete_time is not null'] = null;
                unset($params['delete_time']);
            }
            $params['table'] = $this->table;
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["(shop_goods.name like '%{$params['searchName']}%' or shop_goods.id = '{$params['searchName']}' or shop_goods.code = '{$params['searchName']}')"] = null;
                unset($params['searchName']);
            }
            $params['orderby'] = " sort desc";
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

            $stock = new StockModel();
            $s = $stock->findall(['goods_id' => $app[$i]['id']]);
            if ($s['status'] != 200) {
                $app[$i]['stock'] = array();
            } else {
                $app[$i]['stock'] = $s['data'];
            }
            $cModel = new MerchantCategoryModel();
            $data['id'] = $app[$i]['m_category_id'];
            $c = $cModel->find($data);
            if ($c['status'] == 200) {
                $app[$i]['m_category_name'] = $c['data']['name'];
            } else {
                $app[$i]['m_category_name'] = "分类已删除";
            }
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    public function finds($params)
    {
        //数据库操作
        try {
            $table = new TableModel();
            $params['delete_time is null'] = null;
            $params['table'] = $this->table;
            if (!isset($params['status'])) {
                $params['status'] = 1;
            }
            $params['orderby'] = " sort desc";
            $bool = true;
            if (!isset($params['fields'])) {
                $params['fields'] = " *,(select IFNULL(sum(number),0) from shop_order where  shop_order.goods_id = shop_goods.id) as sold ";
            }
            if (isset($params['stock'])) {
                $bool = $params['stock'];
                unset($params['stock']);
                $params['limit'] = 999;
            } else {
                if (!isset($params['limit']) || empty($params['limit'])) {
                    $params['limit'] = 10;
                }
            }

            if (isset($params['current_page'])) {
                $params['page'] = $params['current_page'];
                unset($params['current_page']);
            } else {
                $params['page'] = 1;
            }
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                // $params["name like '%{$params['searchName']}%'"] = null;
                $params['searchName'] = explode(" ", $params['searchName']);
                for ($i = 0; $i < count($params['searchName']); $i++) {
                    $params["name like '%{$params['searchName'][$i]}%' or code like '%{$params['searchName'][$i]}%'"] = null;
                }
                unset($params['searchName']);
                unset($params['supplier_id']);
            }
            $res = $table->tableList($params);
            $app = $res['app'];
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
            if (isset($app[$i]['update_time']) && $app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
            }
            if (isset($app[$i]['end_time']) && $app[$i]['end_time'] != "") {
                $app[$i]['format_end_time'] = date('m-d H:i:s', $app[$i]['end_time']);
                $app[$i]['format_end_time1'] = date('m:d', $app[$i]['end_time']);
            }
            if (isset($app[$i]['start_time']) && $app[$i]['start_time'] != "") {
                $app[$i]['format_start_time'] = date('m-d H:i:s', $app[$i]['start_time']);
                $app[$i]['format_start_time1'] = date('m:d', $app[$i]['start_time']);
            }
            if (isset($app[$i]['take_goods_time']) && $app[$i]['take_goods_time'] != "" && $app[$i]['take_goods_time'] != 0) {
                $app[$i]['format_take_goods_time'] = date('m-d', $app[$i]['take_goods_time']);
            } else {
                $app[$i]['format_take_goods_time'] = "";
            }
            $app[$i]['pic_urls'] = array_filter(explode(",", $app[$i]['pic_urls']));
            if ($bool == true) {
                $stockData['table'] = 'shop_stock';
                $stockData['goods_id'] = $app[$i]['id'];
                $stockData['delete_time is null'] = null;
                $rs = $table->tablelist($stockData);
                $app[$i]['stock'] = $rs['app'];
            }

//            $sql = "select sum(shop_order.number) as  num from shop_order  where goods_id = {$app[$i]['id']} and confirm_time != 0 ";
//            $sold = $table->querySql($sql);
            if ($app[$i]['sales_number']!=0) {
                $app[$i]['sold'] = $app[$i]['sold'] + intval($app[$i]['sales_number']);
            }else{
                $app[$i]['sold'] = $app[$i]['sold'];
            }
        }

        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => ceil($res['count'] / $params['limit'])];
        }
    }

    /**
     * 查询单条接口
     * 地址:/admin/group/single
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function findOne($params)
    {

        $table = new TableModel();
        //数据库操作
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
        }
//        if (isset($params['searchName'])) {
//            $params['searchNameGoods'] = trim($params['searchNameGoods']);
//            // $params["name like '%{$params['searchName']}%'"] = null;
//            $where["name like '%{$params['searchNameGoods']}%'"] = null;
//            unset($params['searchName']);
//        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
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
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }
            if ($app['end_time'] != "") {
                $app['format_end_time'] = date('Y-m-d H:i:s', $app['end_time']);
            }
            if ($app['start_time'] != "") {
                $app['format_start_time'] = date('Y-m-d H:i:s', $app['start_time']);
            }
            if ($app['take_goods_time'] != "") {
                $app['format_take_goods_time'] = date('Y-m-d', $app['take_goods_time']);
            }
            $stockData['table'] = 'shop_stock';
            $stockData['goods_id'] = $app['id'];
            $stockData['delete_time is null'] = null;
            $rs = $table->tablelist($stockData);
            $app['stock'] = $rs['app'];
            $res = $table->tableSingle("shop_marchant_category", ['id' => $app['m_category_id'], 'delete_time is null' => null]);
            $app['m_category_name'] = $res['name'];
            return result(200, '请求成功', $app);
        }
    }

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
        if (isset($params['status'])) {
            $where['status'] = $params['status'];
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
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }

            $app['label'] = array_filter(explode(",", $app['label']));
            unset($app['detail_info']);
            $stockData['table'] = 'shop_stock';
            $stockData['goods_id'] = $app['id'];
            $stockData['delete_time is null'] = null;
            $rs = $table->tablelist($stockData);
            $app['stock'] = $rs['app'];


            return result(200, '请求成功', $app);
        }
    }

    public function one($params)
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
        if (isset($params['m_category_id'])) {
            $where['m_category_id'] = $params['m_category_id'];
        }
        if (isset($params['storehouse_id'])) {
            $where['storehouse_id'] = $params['storehouse_id'];
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
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }

            $app['label'] = array_filter(explode(",", $app['label']));
            unset($app['detail_info']);
            $stockData['table'] = 'shop_stock';
            $stockData['goods_id'] = $app['id'];
            $stockData['delete_time is null'] = null;
            $rs = $table->tablelist($stockData);
            $app['stock'] = $rs['app'];


            return result(200, '请求成功', $app);
        }
    }

    public function findInfo($params)
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
        $where['delete_time is null'] = null;
        $fields = "";
        if (isset($params['fields'])) {
            $fields = $params['fields'];
        }
        try {
            $app = $table->tableSingle($this->table, $where, $fields);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
        } else {

            return result(200, '请求成功', $app);
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
        try {
            $table = new TableModel();
            $params['create_time'] = time();
            $res = $table->tableAdd($this->table, $params);
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
     * 更新接口
     * 地址:/admin/group/update
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function update($params)
    {

        try {
            //where 条件设置
            if (isset($params['`key`'])) {
                $where['`key`'] = $params['`key`'];
                unset($params['`key`']);
            }
            if (isset($params['merchant_id'])) {
                $where['merchant_id'] = $params['merchant_id'];
                unset($params['merchant_id']);
            }
            if (isset($params['id'])) {
                $where['id'] = $params['id'];
                unset($params['id']);
            }
            $where['delete_time is null'] = null;
            //params 参数值设置
            $params['update_time'] = time();
            //数据库操作
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

    public function updates($params)
    {
        try {
            //where 条件设置
            if (isset($params['`key`'])) {
                $where['`key`'] = $params['`key`'];
                unset($params['`key`']);
            }
            if (isset($params['merchant_id'])) {
                $where['merchant_id'] = $params['merchant_id'];
                unset($params['merchant_id']);
            }
            if (isset($params['id'])) {
                $where['id'] = $params['id'];
                unset($params['id']);
            }
            //params 参数值设置
            $params['update_time'] = time();
            $params['delete_time = null'] = null;
            //数据库操作
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

    //月销
    public function MonthSale($id)
    {

        try {
            $year = date("Y");
            $month = date("m");
            $sql = "select sum(shop_order.number)as num from shop_order  where {$month} = month(curdate()) and {$year} = year(curdate()) and goods_id ={$id}  ";
            $table = new TableModel();
            $res = $table->querySql($sql);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '请求失败');
        } else {
            return result(200, '请求成功', $res[0]['num']);
        }
    }

    //商品总销量
    public function TotalSale($id)
    {
        try {
            $sql = "select sum(shop_order.number) as total, count(shop_order.id) as num from shop_order   where goods_id ={$id}  ";
            $table = new TableModel();
            $res = $table->querySql($sql);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '请求失败');
        } else {
            return result(200, '请求成功', $res[0]);
        }
    }

    public function goodsOut()
    {
        $table = new TableModel();
        $sql = "UPDATE shop_goods set status = 0  where id in (select sg.id from (select id from shop_goods  where stocks = 0)sg );";
        $res = yii::$app->db->createCommand($sql)->execute();
        return $res;
    }

    /**
     * 通过video_id查询数据
     * @param $params
     * @return array
     * @throws Exception
     */
    public function findByVideoId($params)
    {
        $table = new TableModel();
        if (isset($params['video_id'])) {
            $where['video_id'] = $params['video_id'];
        } else {
            return result(500, '缺少video_id');
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
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }
            $app['label'] = array_filter(explode(",", $app['label']));
            unset($app['detail_info']);
            $stockData['table'] = 'shop_stock';
            $stockData['goods_id'] = $app['id'];
            $rs = $table->tablelist($stockData);
            $app['stock'] = $rs['app'];


            return result(200, '请求成功', $app);
        }
    }

}
