<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\merchant\app;

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
class AppAccessModel extends TableModel {

    /**
     * 查询列表接口
     * 地址:/admin/group/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function findall($params) {
        //数据库操作

        try {
            $table = new TableModel();
            $params['delete_time is null'] = null;
            $params['table'] = "system_app_access";
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
            return result(204, '未找到对应数据');
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
        $params['delete_time is null'] = null;
        if(isset($params['key'])){
            $params['`key`'] = $params['key'];
            unset($params['key']);
        }
        $fields = "";
        if (isset($params['fields'])) {
            $fields = $params['fields'];
            unset($params['fields']);
        }
        try {
            $app = $table->tableSingle('system_app_access', $params, $fields);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '未找到对应数据');
        } else {
            if (isset($app['create_time'])) {
                $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
                if ($app['update_time'] != "") {
                    $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
                }
            }
//            $rs = $table->tableSingle('system_app', ['id' => $app['app_id']]);
//            $app['category_id'] = $rs['category_id'];
            return result(200, '请求成功', $app);
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
        $status = 0;
        if (isset($params['status'])) {
            $status = $params['status'];
        }
        $data = [
            'name' => isset($params['name']) ? $params['name'] : "",
            'app_id' => isset($params['pic_url']) ? $params['app_id'] : "",
            'merchant_id' => isset($params['merchant_id']) ? $params['merchant_id'] : "",
            'combo_id' => isset($params['combo_id']) ? $params['combo_id'] : "",
            'expire_time' => isset($params['expire_time']) ? $params['expire_time'] : "",
            'pic_url' => isset($params['pic_url']) ? $params['pic_url'] : "",
            '`key`' => isset($params['key']) ? $params['key'] : "",
            'type' => isset($params['type']) ? $params['type'] : "",
            'detail_info' => isset($params['detail_info']) ? $params['detail_info'] : "",
            'config' => isset($params['config']) ? $params['config'] : "",
            'shop_category_id' => isset($params['shop_category_id']) ? $params['shop_category_id'] : "",
            'status' => $status,
            'create_time' => time()
        ];
        try {
            //数据库操作
            $table = new TableModel();
            $res = $table->tableAdd('system_app_access', $data);
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
    public function delete($params) {
        //where条件设置
        $where = ['id' => $params['id']];
        //params 参数设置
        unset($params['id']);
        $params['delete_time'] = time();
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableUpdate('system_app_access', $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(204, '删除失败');
        } else {
            return result(200, '请求成功', $res);
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
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
        }
        unset($params['id']);
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        $table = new TableModel();
       
            $res = $table->tableUpdate('system_app_access', $params, $where); try {
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }

        if (!$res) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

     public function upd($params) {
        //where 条件设置
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
        }
        unset($params['id']);
        //params 参数值设置
        $params['update_time'] = time();
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableUpdate('system_app_access', $params, $where);
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
     * 查询列表接口
     * 地址:/admin/group/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function finds($params) {
        //数据库操作
        try {
            $table = new TableModel();
            $sql = "SELECT saa.id as saa_id,saa.`key` as saa_key,saa.name as saa_name,sa.category_id as category_id, sac.id as combo_id,sa.`name`as app_name,saa.pic_url as saa_pic_url,sac.`name` as combo_name,saa.expire_time FROM `system_app_access` as saa  INNER JOIN system_app as sa on sa.id = saa.app_id  INNER JOIN  system_app_combo as sac on sac.id = saa.combo_id   where saa.merchant_id = {$params['mid']}";
            $app = $table->querySql($sql);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        $time = time();
        for ($i = 0; $i < count($app); $i++) {
            if ($time > $app[$i]['expire_time']) {
                $app[$i]['expire_time'] = "已过期";
            } else {
                $app[$i]['expire_time'] = date('Y-m-d', $app[$i]['expire_time']);
            }
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app];
        }
    }

}
