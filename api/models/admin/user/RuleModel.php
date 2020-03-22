<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact rule@swoft.org
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
class RuleModel extends TableModel {

    /**
     * 查询列表接口
     * 地址:/admin/rule/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function findall($params) {
        //数据库操作
        $table = new TableModel();

        try {
            $params['delete_time is null'] = null;
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["name like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }
            $params['table'] = "system_auth_rule";
            $params['orderby'] = " `name`,sort ";
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
            return ['status' => 204, 'message' => '未找到对应数据',];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

    /**
     * 查询单条接口
     * 地址:/admin/rule/single
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function find($params) {

        $table = new TableModel();
        //数据库操作
        try {
            $app = $table->tableSingle('system_auth_rule', ['id' => $params['id'], 'delete_time is null' => null]);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return ['status' => 204, 'message' => '未找到对应数据',];
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
     * 地址:/admin/rule/add
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function add($params) {
        //data 新增数据参数设置
        $data = [
            'pid' => isset($params['pid']) ? $params['pid'] : 0,
            'menu_name' => isset($params['menu_name']) ? $params['menu_name'] : "",
            'name' => $params['name'],
            'title' => $params['title'],
            'type' => $params['type'],
            'rule_type' => $params['rule_type'],
            '`condition`' => $params['condition'],
            'icon' => $params['icon'],
            'menu_url' => isset($params['menu_url']) ? $params['menu_url'] : "",
            'sort' => $params['sort'],
            '`status`' => $params['status'],
            'create_time' => time()
        ];
        //数据库操作
        $table = new TableModel();

        try {
            $res = $table->tableAdd('system_auth_rule', $data);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return ['status' => 500, 'message' => '新增失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res];
        }
    }

    /**
     * 删除接口
     * 地 址:/admin/rule/delete
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
            $res = $table->tableUpdate('system_auth_rule', $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return ['status' => 204, 'message' => '删除失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res];
        }
    }

    /**
     * 更新接口
     * 地址:/admin/rule/update
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function update($params) {
        //where 条件设置

        try {
            $where = ['id' => $params['id']];
            unset($params['id']);
            if (isset($params['rule_type'])) {
                if ($params['rule_type'] != 1) {
                    $params['menu_url'] = "";
                    $params['menu_name'] = "";
                }
            }
            $params['update_time'] = time();
            //数据库操作
            $table = new TableModel();
            if (isset($params['condition'])) {
                $params['`condition`'] = $params['condition'];
                unset($params['condition']);
            }
            if (isset($params['status'])) {
                $params['`status`'] = $params['status'];
                unset($params['status']);
            }
            $res = $table->tableUpdate('system_auth_rule', $params, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }

        if (!$res) {
            return ['status' => 500, 'message' => '更新失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功'];
        }
    }

    /**
     * 在角色中查询列表接口
     * 地址:/admin/rule/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function rule($params) {


        $table = new TableModel();
        $fields = 'id,pid,name,title';
        $app = $table->tableList('system_auth_rule', ["delete_time is null" => null, 'pid' => "0"], $fields);
        $rules = $table->tableSingle('system_auth_rule', ["delete_time is null" => null, 'id' => $params['id']]);
        $str = "";
        for ($k = 0; $k < count($app); $k++) {
            $app1 = $table->tableList('system_auth_rule', ["delete_time is null" => null, "pid " => $app[$k]['id']], $fields);
            for ($w = 0; $w < count($app1); $w++) {
                $app1[$w]['checkbox'] = 0;
            }
            $rules['rules'] = substr($rules['rules'], 0, strlen($rules['rules']) - 1);
            $arr = explode(",", $rules['rules']);
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
            return ['status' => 204, 'message' => '查询失败'];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app,];
        }
    }

//    public function findmenu() {
//        $userid = 1;
//        //数据库操作
//
//        $table = new TableModel();
//        try {
////            $access = $table->tableSingle('admin_auth_group_access', ['delete_time is null' => null]);
////            $group = $table->tableSingle('admin_auth_group', ['delete_time is null' => null, 'id' => $access['group_id']]);
//            //     $rules = explode(',', substr($group['rules'], 0, strlen($group['rules']) - 1));
//            //$authRule = $table->tableList('admin_auth_rule', ['delete_time is null' => null], '', ' sort asc');
//            //$length = count($rules);
//            $pmenus = $table->tableList('system_auth_rule', ['delete_time is null' => null, 'pid' => 0], 'id,name,title,rule_type,icon', ' sort asc');
//            for ($i = 0; $i < count($pmenus); $i++) {
//                $tmenus = $table->tableList('system_auth_rule', ['delete_time is null' => null, 'pid' => $pmenus[$i]['id']], 'id,pid,name,rule_type,title,icon', ' sort asc');
//
//                $pmenus[$i]['children'] = $tmenus;
//                for ($k = 0; $k < count($tmenus); $k++) {
//                    $menus = $table->tableList('admin_auth_rule', ['delete_time is null' => null, 'pid' => $tmenus[$k]['id'],], 'id,pid,name,title,rule_type,icon', ' sort asc');
//                    $pmenus[$i]['children'][$k]['children'] = $menus;
////                    if ($menus) {
////                        $pmenus[$i]['list'][$k]['list'] = $menus;
////                    }
////                    $array = array();
////                    for ($j = 0; $j < $length; $j++) {
////                        for ($t = 0; $t < count($menus); $t++) {
////                            if ($menus[$t]['id'] == $rules[$j]) {
////                                $array[] = $menus[$t];
////                            }
////                            if (count($array) != 0) {
////                                $pmenus[$i]['list'][$k]['list'] = $array;
////                            }
////                        }
////                    }
//                }
//            }
////            for ($i = 0; $i < count($pmenus); $i++) {
////                unset($pmenus[$i]['id']);
////                for ($j = 0; $j < count($pmenus[$i]); $j++) {
////                    unset($pmenus[$i]['list'][$j]['id']);
////                    unset($pmenus[$i]['list'][$j]['pid']);
////                    if (isset($pmenus[$i]['list'][$j])) {
////                        for ($k = 0; $k < count($pmenus[$i]['list'][$j]['list']); $k++) {
////                            unset($pmenus[$i]['list'][$j]['list'][$k]['id']);
////                            unset($pmenus[$i]['list'][$j]['list'][$k]['pid']);
////                        }
////                    }
////                }
////            }
////            for ($i = 0; $i < count($pmenus); $i++) {
////                for ($j = 0; $j < count($pmenus[$i]); $j++) {
////                    if (!isset($pmenus[$i]['list'][$j]['list'])) {
////                        unset($pmenus[$i]['list'][$j]);
////                    }
////                }
////                if (count($pmenus[$i]['list']) == 0) {
////                    unset($pmenus[$i]['list']);
////                    if (!isset($pmenus[$i]['list'])) {
////                        unset($pmenus[$i]);
////                    }
////                }
////            }
//        } catch (Exception $ex) {
//            return [
//                'status' => '500',
//                'message' => $ex,
//            ];
//        }
//
//        if (empty($pmenus)) {
//            return ['status' => 204, 'message' => '查询失败',];
//        } else {
//            return ['status' => 200, 'message' => '请求成功', 'data' => $pmenus,];
//        }
//    }

    public function findMenu() {
        $userid = 1;
        //数据库操作

        $table = new TableModel();
        try {
            $params['table'] = "system_auth_rule";
            $params['delete_time is null'] = null;
            $params['pid'] = 0;
            $params['fields'] = "id,name,title,rule_type,icon";
            $params['orderby'] = " sort asc ";
            $pmenus = $table->tableList($params);

            for ($i = 0; $i < count($pmenus['app']); $i++) {
                $params['pid'] = $pmenus['app'][$i]['id'];
                $tmenus = $table->tableList($params);
                $pmenus[$i]['children'] = $tmenus['app'];
                for ($k = 0; $k < count($tmenus['app']); $k++) {
                    $params['pid'] = $tmenus[$k]['id'];
                    $menus = $table->tableList($params);
                    $pmenus[$i]['children'][$k]['children'] = $menus['app'];
                }
            }
        } catch (Exception $ex) {
            return [
                'status' => '500',
                'message' => $ex,
            ];
        }

        if (empty($pmenus)) {
            return ['status' => 204, 'message' => '查询失败',];
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $pmenus,];
        }
    }

}
