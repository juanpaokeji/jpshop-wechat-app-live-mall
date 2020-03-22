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
use yii;
use yii\db\Exception;
use app\models\core\TableModel;

class UserModel extends TableModel
{

    public $table = 'system_sub_admin';

    /** 查询列表
     * $table 为必传参
     *
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     * @return array
     */
    public $tableName = 'system_sub_admin';

    /** 查询列表
     * $table 为必传参
     *
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     * @return array
     */
    public function findAll($params)
    {

        try {

            $table = new TableModel();
            $params['system_sub_admin.delete_time is null'] = null;
            $params['fields'] = 'system_sub_admin.id,username,real_name,intro,system_sub_admin.status,phone,system_sub_admin.status,type,system_sub_admin.leader';
            if ($params['type'] != 1) {
                $params['join'] = " inner join shop_auth_group_access on uid=id  inner join shop_auth_group  on group_ids = shop_auth_group.id";
                $params['shop_auth_group.is_kefu'] = 0;
            }
            $params['system_sub_admin.`key`'] = $params['`key`'];
            $params['system_sub_admin.merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
            unset($params['`key`']);

            if (isset($params['status'])) {
                $params['system_sub_admin.status'] = $params['status'];
                unset($params['status']);
            }
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["username like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }
            $params['orderby'] = "  system_sub_admin.id desc";
            $params['table'] = $this->tableName;
            $res = $table->tableList($params);
            $app = $res['app'];
            foreach ($app as $k => $v) {
                //循环获取 角色 名称
                $groupAccessRes = $table->tableSingle('shop_auth_group_access', ['uid' => $v['id'], 'delete_time is null' => null], 'group_ids');
                if ($groupAccessRes) {
                    $groupRes = $table->tableSingle('shop_auth_group', ['id' => $groupAccessRes['group_ids']], 'title');
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
        } catch (\Exception $e) {
            return result(500, '数据库操作失败');
        }
    }

    public function kefu($params)
    {

        try {
            $table = new TableModel();

            $rule = $table->tableSingle('shop_auth_group', ['is_kefu' => 1, '`key`' => $params['`key`'], 'merchant_id' => $params['merchant_id']]);
            $params['system_sub_admin.delete_time is null'] = null;
            $params['fields'] = 'id,username,real_name,intro,status,phone';
            $params['join'] = " inner join shop_auth_group_access on uid=id ";
            $params['system_sub_admin.`key`'] = $params['`key`'];
            $params['system_sub_admin.merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
            unset($params['`key`']);
            $params['group_ids'] = $rule['id'];
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

            return [
                'status' => 200,
                'message' => '请求成功',
                'data' => $app,
                'count' => $res['count'],
            ];
        } catch (\Exception $e) {
            return result(500, '数据库操作失败');
        }
    }

    /** 查询单条
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     * @return array
     */
    public function find($params)
    {

        try {
            $table = new TableModel();
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
            $groupAccessRes = $table->tableSingle('shop_auth_group_access', ['uid' => $res['id'], 'delete_time is null' => null], 'group_ids');
            if ($groupAccessRes) {
                $groupRes = $table->tableSingle('shop_auth_group', ['id' => $groupAccessRes['group_ids']], 'title');
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
    public function one($params)
    {
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
    public function add($params)
    {
        //存入 user 表数据
        try {

            $save = [
                '`key`' => $params['`key`'],
                'merchant_id' => $params['merchant_id'],
                'username' => $params['username'],
                'real_name' => $params['real_name'],
                'password' => md5($params['password'] . $params['salt']),
                'salt' => $params['salt'],
                'type' => $params['type'],
                'intro' => $params['intro'],
                'self_leader_id' => isset($params['self_leader_id']) ? $params['self_leader_id'] : 0,
                'leader' => isset($params['leader']) ? $params['leader'] : '',
                'points' => isset($params['points']) ? $params['points'] : 0,
                'status' => $params['status'],
                'create_time' => time()
            ];
            //开始事务
            $transaction = Yii::$app->db->beginTransaction();
            $table = new TableModel();
            $res = $table->tableAdd($this->tableName, $save);
            $groupAccessData = [
                'uid' => $res,
                '`key`' => $params['`key`'],
                'merchant_id' => $params['merchant_id'],
                'group_ids' => isset($params['group_id']) ? $params['group_id'] : 0,
                'create_time' => time()
            ];
            $table->tableAdd('shop_auth_group_access', $groupAccessData);
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
    public function delete($params)
    {
        $table = new TableModel();
        //开始事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $table->tableUpdate($this->tableName, ['delete_time' => time()], ['id' => $params['id']]); //软删除，只更新 删除时间 字段
            $table->tableUpdate('shop_auth_group_access', ['delete_time' => time()], ['uid' => $params['id']]);
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
    public function update($params)
    {
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
                'real_name' => $params['real_name'],
                'intro' => $params['intro'],
                'points' => isset($params['points']) ? $params['points'] : 0,
                'self_leader_id' => isset($params['self_leader_id']) ? $params['self_leader_id'] : 0,
                'leader' => isset($params['leader']) ? $params['leader'] : '',
                'type' => $params['type'],
                'status' => $params['status'],
                'update_time' => time()
            ];
            if ($params['password'] != "") {
                $data['password'] = md5($params['password'] . $params['salt']);
                $sql = "update wolive_service set password = {$data['password']} where user_name = {$data['username']}";
            }
            //开始事务
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $table->tableUpdate($this->tableName, $data, $where);
                $table->tableUpdate('shop_auth_group_access', ['group_ids' => $params['group_id'], 'update_time' => time()], ['uid' => $params['id']]);
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
    public function updateInfo($params)
    {
        $where = ['id' => $params['id']];
        $table = new TableModel();
        $data = [
            'real_name' => $params['real_name'],
            'username' => $params['username'],
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
     * 修改门店易联云配置信息
     */
    public function ylyupdate($params)
    {
        $where = ['id' => $params['id']];
        $table = new TableModel();
        $data = [
            'yly_config' => isset($params['yly_config']) ? $params['yly_config'] : '',
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
    public function updatePassword($params)
    {
        $where = ['id' => $params['id']];
        $table = new TableModel();
        $data = [
            'password' => md5($params['password'] . $params['salt']),
            'update_time' => time()
        ];
        $table->tableUpdate($this->tableName, $data, $where);
        return result(200, '请求成功');
    }

    public function more($params){
        try {
            $table = new TableModel();
            $params['delete_time is null'] = null;
            $params['fields'] = '*';
            $params['orderby'] = " id desc";
            $params['table'] = $this->tableName;
            $res = $table->tableList($params);
            $app = $res['app'];
            return [
                'status' => 200,
                'message' => '请求成功',
                'data' => $app,
                'count' => $res['count'],
            ];
        } catch (\Exception $e) {
            return result(500, '数据库操作失败');
        }
    }
    
    public function updates($params){
    	$where = ['id' => $params['id']];
    	unset($params['id']);
        $table = new TableModel();
        $data = $params;
        $table->tableUpdate($this->tableName, $data, $where);
        return result(200, '请求成功');
    }

}
