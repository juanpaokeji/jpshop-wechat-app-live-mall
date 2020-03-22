<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\admin\voucher;

//引入各表实体
use app\models\core\TableModel;
use yii\db\Exception;

/**
 *
 * @version   2018年04月16日 抵用卷活动
 * @author    YangJing <120912212@qq.com>
 * @copyright Copyright 2018 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class VoucherTypeModel extends TableModel {

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
            $params['delete_time is null'] = null;
            $params['table'] = "system_voucher_type";
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["name like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }
            $res = $table->tableList($params);
            $app = $res['app'];
        } catch (Exception $ex) {
            return [
                'status' => '500',
                'message' => '数据库操作失败',
            ];
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d', $app[$i]['update_time']);
            }
            if ($app[$i]['from_date'] != "") {
                $app[$i]['from_date'] = date('Y-m-d', $app[$i]['from_date']);
            }
            if ($app[$i]['to_date'] != "") {
                $app[$i]['to_date'] = date('Y-m-d', $app[$i]['to_date']);
            }
            if ($app[$i]['set_online_time'] != "") {
                $app[$i]['set_online_time'] = date('Y-m-d', $app[$i]['set_online_time']);
            }
            $rs = $table->tableSingle('system_voucher_channel', ['id' => $app[$i]['act_id']]);
            $app[$i]['act_id'] = $rs['act_name'];
        }
        if (empty($app)) {
            return ['status' => 204, 'message' => '未找到对应数据',];
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
    public function find($params) {

        $table = new TableModel();
        //数据库操作
        try {
            $app = $table->tableSingle('system_voucher_type', ['id' => $params['id'], 'delete_time is null' => null]);
        } catch (Exception $ex) {
            return json_encode(['status' => '500', 'message' => '数据库操作失败',], JSON_UNESCAPED_UNICODE);
        }
        if (gettype($app) != 'array') {
            return ['status' => 204, 'message' => '未找到对应数据',];
        } else {
            $app['create_time'] = date('Y-m-d', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d', $app['update_time']);
            }
            if ($app['from_date'] != "") {
                $app['from_date'] = date('Y-m-d', $app['from_date']);
            }
            if ($app['to_date'] != "") {
                $app['to_date'] = date('Y-m-d', $app['to_date']);
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
        //数据库操作
        $table = new TableModel();
        try {

            $params['create_time'] = time();
            $res = $table->tableAdd('system_voucher_type', $params);
        } catch (Exception $ex) {
            return json_encode(['status' => '500', 'message' => '数据库操作失败',], JSON_UNESCAPED_UNICODE);
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
            $res = $table->tableUpdate('system_voucher_type', $params, $where);
        } catch (Exception $ex) {
            return json_encode(['status' => '500', 'message' => '数据库操作失败',], JSON_UNESCAPED_UNICODE);
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
            $res = $table->tableUpdate('system_voucher_type', $params, $where);
        } catch (Exception $ex) {
            return json_encode(['status' => '500', 'message' => '数据库操作失败',], JSON_UNESCAPED_UNICODE);
        }

        if (!$res) {
            return ['status' => 500, 'message' => '更新失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功'];
        }
    }

    public function setInc($sql) {
        //数据库操作
        $table = new TableModel();
        try {
            $sql = "update system_voucher_type set update_time = '" . time() . "" . $sql;
            $res = $table->querySql($sql);
        } catch (Exception $ex) {
            return json_encode(['status' => '500', 'message' => '数据库操作失败',], JSON_UNESCAPED_UNICODE);
        }

        if (!$res) {
            return ['status' => 500, 'message' => '更新失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功'];
        }
    }

}
