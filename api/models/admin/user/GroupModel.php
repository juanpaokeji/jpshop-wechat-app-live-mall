<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\admin\user;

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
class GroupModel extends TableModel {

    /**
     * 查询列表接口
     * 地址:/admin/group/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function findall($params) {
        //数据库操作
        $table = new TableModel();
        $params['delete_time is null'] = null;

        try {
            $params['delete_time is null'] = null;
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["name like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }
            $params['table'] = "system_auth_group";
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
        try {
            $app = $table->tableSingle('system_auth_group', ['id' => $params['id'], 'delete_time is null' => null]);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '未找到对应数据');
        } else {
            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }
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
        $rules = "";
        if (isset($params['rules'])) {
            for ($i = 0; $i < count($params['rules']); $i++) {
                $rules .= $params['rules'][$i] . ",";
            }
        }

        $status = 0;
        if (isset($params['status'])) {
            $status = $params['status'];
        }

        $data = [
            'title' => $params['title'],
            'status' => $status,
            'type' => $params['type'],
            'rules' => $rules,
            'create_time' => time()
        ];
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableAdd('system_auth_group', $data);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '新增失败');
        } else {
            return result(200, '请求成功');
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
            $res = $table->tableUpdate('system_auth_group', $params, $where);
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
    public function update($params) {


        //where 条件设置
        $where = ['id' => $params['id']];
        unset($params['id']);

        //params 参数值设置
        if (isset($params['rules'])) {
            $rules = "";
            if ($params['rules'] != "") {
                for ($i = 0; $i < count($params['rules']); $i++) {
                    $rules .= $params['rules'][$i] . ",";
                }
            }
            $params['rules'] = $rules;
        }
        $params['update_time'] = time();
        //数据库操作
        $table = new TableModel();
        try {
            $res = $table->tableUpdate('system_auth_group', $params, $where);
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
     * 在角色中查询列表接口
     * 地址:/admin/rule/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function rule($params) {

        if (!isset($params['id'])) {
            return result(400, '缺少参数 id');
            return json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        $table = new TableModel();
        $fields = 'id,pid,name,title';
        $app = $table->tableList('system_auth_rule', ["delete_time is null" => null, 'pid' => "0"], $fields);
        $groups = $table->tableSingle('system_auth_group', ["delete_time is null" => null, 'id' => $params['id']]);
        $str = "";
        for ($k = 0; $k < count($app); $k++) {
            $app1 = $table->tableList('system_auth_rule', ["delete_time is null" => null, "pid " => $app[$k]['id']], $fields);
            for ($w = 0; $w < count($app1); $w++) {
                $app1[$w]['checkbox'] = 0;
            }
            $groups['rules'] = substr($groups['rules'], 0, strlen($groups['rules']) - 1);
            $arr = explode(",", $groups['rules']);
            for ($i = 0; $i < count($arr); $i++) {
                for ($j = 0; $j < count($app1); $j++) {
                    if ($arr[$i] == $app1[$j]['id']) {
                        $app1[$j]['checkbox'] = 1;
                    }
                }
            }
            $app[$k]['data'] = $app1;
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
        } else {
            return result(200, '请求成功', $app);
        }
    }

    /**
     * 查询角色成员列表接口
     * 地址:/admin/message/signature/list
     * @return array
     */
    public function users($params) {
        $where = [];
        // unset($params['page']); //必须传的两个参，不需要
        //  unset($params['limit']);
        if (!empty($params)) {
            $where['group_id'] = $params['group_id'];
        }
        $where['delete_time is null'] = null; //没有被删除
        $fields = 'uid,create_time';
        $table = new TableModel();
        try {
            $res = $table->tableList('system_auth_group_access', $where, $fields);
        } catch (\Exception $e) {
            return result(500, '数据库操作失败');
        }
//        $res = $table->tableList('admin_user', $where, $fields);
        if (gettype($res) != 'array') {
            return result(500, '查询失败');
        }

        foreach ($res as $k => $v) {
            //循环获取 用户 名称
            try {
                $users = $table->tableSingle('admin_user', ['id' => $v['uid']], 'username');
                $res[$k]['uid'] = $v['uid'];
                $res[$k]['username'] = $users['username'];
            } catch (\Exception $e) {
                return result(500, '数据库操作失败');
            }
        }
        return result(200, '请求成功', $res);
    }

}
