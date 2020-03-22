<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\pay;

//引入各表实体
use app\models\core\TableModel;

/**
 *
 * @version   2018年04月16日
 * @author    YangJing <120912212@qq.com>
 * @copyright Copyright 2018 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class WeixinModel extends TableModel {

    /**
     * 查询列表接口
     * 地址:/admin/group/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function findall($params) {
        //数据库操作
        $table = new TableModel();
        try {
            $app = $table->tableList('system_pay_weixin', ['delete_time is null' => null]);
        } catch (Exception $ex) {
            return [
                'status' => '500',
                'message' => '数据库操作失败',
            ];
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
            }
        }
        if (gettype($app) != 'array') {
            return ['status' => 204, 'message' => '查询失败',];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app,];
        }
    }

    /**
     * 查询单条接口
     * 地址:/admin/group/single
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function find($params) {

        $table = new TableModel();
        //数据库操作
        try {
            $app = $table->tableSingle('system_pay_weixin', ['id' => $params['id'], 'delete_time is null' => null]);
        } catch (Exception $ex) {
            return ['status' => '500', 'message' => '数据库操作失败',];
        }
        if (gettype($app) != 'array') {
            return ['status' => 204, 'message' => '查询失败',];
        } else {
            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }
            return ['status' => 200, 'message' => '请求成功', 'data' => $app];
        }
    }

    /**
     * 新增接口
     * 地址:/admin/group/add
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function add($params) {
        //data 新增数据参数设置
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableAdd('system_pay_weixin', $params);
        } catch (Exception $ex) {
            return ['status' => '500', 'message' => '数据库操作失败',];
        }
        if (!$res) {
            return ['status' => 500, 'message' => '新增失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功'];
        }
    }

    /**
     * 删除接口
     * 地 址:/admin/group/delete
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function delete($params) {
        //where条件设置
        $where = ['id' => $params['id']];
        //params 参数设置
        unset($params['id']);
        $params['delete_time'] = time();
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableUpdate('system_pay_weixin', $params, $where);
        } catch (Exception $ex) {
            return ['status' => '500', 'message' => '数据库操作失败',];
        }
        if (!$res) {
            return ['status' => 204, 'message' => '删除失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res];
        }
    }

    /**
     * 更新接口
     * 地址:/admin/group/update
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function update($params) {
        //where 条件设置
        $where = ['id' => $params['id']];
        unset($params['id']);
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableUpdate('system_pay_weixin', $params, $where);
        } catch (Exception $ex) {
            return ['status' => '500', 'message' => '数据库操作失败',];
        }

        if (!$res) {
            return ['status' => 500, 'message' => '更新失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功'];
        }
    }

}
