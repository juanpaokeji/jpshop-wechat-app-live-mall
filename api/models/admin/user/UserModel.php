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
use yii;
use yii\db\Exception;
use app\models\core\TableModel;

/**
 * 通用表格操作 model
 *
 * @version   2018年03月19日
 * @author    JYS <272074691@qq.com>
 * @copyright Copyright 2010-2016 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class UserModel extends TableModel {

    public $tableName = 'admin_user';

    /** 查询列表
     * $table 为必传参
     *
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     * @return array
     */
    public function findAll($params) {
        $table = new TableModel();
        $params['delete_time is null'] = null;
        $params['fields'] = 'id,username,status,phone';
        if (isset($params['searchName'])) {
            $params['searchName'] = trim($params['searchName']);
            $params["username like '%{$params['searchName']}%'"] = null;
            unset($params['searchName']);
        }
        $params['table'] = $this->tableName;
        $res = $table->tableList($params);
        $app = $res['app'];
        if (empty($app)) {
            return result(200, '请求成功', []);
        }
        foreach ($app as $k => $v) {
            //循环获取 角色 名称
            $groupAccessRes = $table->tableSingle('system_auth_group_access', ['uid' => $v['id'], 'delete_time is null' => null, 'type' => 1], 'group_ids');
            if ($groupAccessRes && $groupAccessRes['group_ids']) {
                $groupRes = $table->tableSingle('system_auth_group', ['id' => $groupAccessRes['group_ids']], 'title');
                $app[$k]['title'] = $groupRes['title'];
            } else {
                $app[$k]['title'] = '';
            }
        }
        return [
            'status' => 200,
            'message' => '请求成功',
            'data' => $app,
            'count' => $res['count'],
        ];
        try {
            
        } catch (\Exception $e) {
            return result(500, '数据库操作失败');
        }
    }

    /** 查询单条
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     * @return array
     */
    public function find($params) {
        $table = new TableModel();
        try {
            $where = $params;
            $where['delete_time is null'] = null; //没有被删除
            $res = $table->tableSingle($this->tableName, $where);
            if (empty($res)) {
                return [
                    'status' => '200',
                    'message' => '请求成功',
                    'data' => []
                ];
            }
            $groupAccessRes = $table->tableSingle('system_auth_group_access', ['uid' => $res['id'], 'delete_time is null' => null, 'type' => 1], 'group_ids');
            if ($groupAccessRes) {
                $groupRes = $table->tableSingle('system_auth_group', ['id' => $groupAccessRes['group_ids']], 'title');
                $res['group_ids'] = $groupAccessRes['group_ids'];
                $res['title'] = $groupRes['title'];
            } else {
                $res['group_ids'] = 0;
                $res['title'] = '';
            }
            return result(200, '请求成功', $res);
        } catch (\Exception $e) {
            return result(500, '数据库操作失败');
        }
    }

    /** 查询单条
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     * @return array
     */
    public function one($params) {
        $table = new TableModel();

        try {
            $where['delete_time is null'] = null; //没有被删除
            $where['id'] = $params['id'];
            $res = $table->tableSingle($this->tableName, $where);
            if (!empty($res)) {
                return result(200, '请求成功', $res);
            } else {
                return result(500, '查询失败');
            }
        } catch (\Exception $e) {
            return result(500, '数据库操作失败');
        }
    }

    /**
     * 新增
     * 创建用户的时候，随机生成一个32位随机数然后md5获取salt值，将用户输入的md5(password+salt)存入password
     * @param array|null $params
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function add($params) {
        //存入 user 表数据
        $save = [
            'username' => $params['username'],
            'password' => md5($params['password'] . $params['salt']),
            'salt' => $params['salt'],
            'intro' => $params['intro'],
            'status' => $params['status'],
            'create_time' => time()
        ];
        //开始事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $table = new TableModel();
            $res = $table->tableAdd($this->tableName, $save);
            $groupAccessData = [
                'uid' => $res,
                'type' => 1,
                'group_ids' => $params['group_id'],
                'create_time' => time()
            ];
            $table->tableAdd('system_auth_group_access', $groupAccessData);
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, '添加失败');
        }
        return result(200, '请求成功', $res);
        //请求成功示例 {"status":"200","message":"请求成功","data":4}
        //请求失败示例 {"status":"500","message":"1001 添加失败"}
    }

    /**
     * @param array|null $params
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function delete($params) {
        $table = new TableModel();
        ;
        //开始事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $table->tableUpdate($this->tableName, ['delete_time' => time()], ['id' => $params['id']]); //软删除，只更新 删除时间 字段
            $table->tableUpdate('system_auth_group_access', ['delete_time' => time()], ['uid' => $params['id'], 'type' => 1]);
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
        } catch (\Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, '删除失败');
        }
        return result(200, '请求成功');
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1002 删除失败"}
    }

    /**
     * @param array|null $params
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function update($params) {
        $where = ['id' => $params['id']];
        $table = new TableModel();
        if (!isset($params['username'])) {
            $data = [
                'status' => $params['status'],
            ];
            try {
                $table->tableUpdate($this->tableName, $data, $where);
            } catch (\Exception $e) {
                return result(500, '更新失败');
            }
        } else {
            $data = [
                'username' => $params['username'],
                'intro' => $params['intro'],
                'status' => $params['status'],
                'update_time' => time()
            ];
            if ($params['password'] != "") {
                $data['password'] = md5($params['password'] . $params['salt']);
            }
            //开始事务
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $table->tableUpdate($this->tableName, $data, $where);
                $table->tableUpdate('system_auth_group_access', ['group_ids' => $params['group_id'], 'update_time' => time()], ['uid' => $params['id'], 'type' => 1]);
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            } catch (\Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, '更新失败');
            }
        }
        return result(200, '请求成功');
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1003 更新失败"}
    }

    /**
     * @param array|null $params
     * @throws Exception if the model cannot be found
     * @return array
     * 修改当前登陆人员信息
     */
    public function updateInfo($params) {
        $where = ['id' => $params['id']];
        $table = new TableModel();
        $data = [
            'real_name' => $params['real_name'],
            'phone' => $params['phone'],
            'intro' => $params['intro'],
            'update_time' => time()
        ];
        $table->tableUpdate($this->tableName, $data, $where);
        return result(200, '请求成功');
    }

    /**
     * @param array|null $params
     * @throws Exception if the model cannot be found
     * @return array
     * 修改当前登陆人员密码
     */
    public function updatePassword($params) {
        $where = ['id' => $params['id']];
        $table = new TableModel();
        $data = [
            'password' => $params['password'],
            'update_time' => time()
        ];
        $table->tableUpdate($this->tableName, $data, $where);
        return result(200, '请求成功');
    }

}
