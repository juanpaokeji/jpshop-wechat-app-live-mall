<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\merchant\system;

//引入各表实体
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
class UnitModel extends TableModel
{

    public $table = "system_unit";

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
            $params['delete_time is null'] = null;
            $params['table'] = $this->table;
            $params['status'] = 1;
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
        $time = time();
        for ($i = 0; $i < count($app); $i++) {

            if ($time > $app[$i]['expire_time']) {
                $app[$i]['expire_time'] = 0;
            } else {
                $app[$i]['expire_time'] = 1;
            }
        }
        //返回数据 时间格式重置
//        for ($i = 0; $i < count($app); $i++) {
//            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
//            if ($app[$i]['update_time'] != "") {
//                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
//            }
//        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    /**
     * 查询单条接口
     * 地址:/admin/group/single
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function find($params)
    {

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
        if (isset($params['route'])) {
            $where['route'] = $params['route'];
            unset($params['route']);
        }
        $where['delete_time is null'] = null;
        //数据库操作
        try {
            $table = new TableModel();
            $res = $table->tableSingle($this->table, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '请求失败');
        } else {
            return result(200, '请求成功', $res);
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
            return result(500, '未找到对应数据');
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
        //where 条件设置
        $where = ['id' => $params['id']];
        unset($params['id']);
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        $table = new TableModel();
        try {
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

    public function updateStatus($params)
    {

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
        if (isset($params['route'])) {
            $where['route'] = $params['route'];
            unset($params['route']);
        }
        $where['delete_time is null'] = null;
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
            return result(200, '请求成功', $res);
        }
    }

}
