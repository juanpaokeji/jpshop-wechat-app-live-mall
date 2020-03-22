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
class WeChatConfigModel extends TableModel {
    public $tableName = 'system_wx_config';

    /** 查询列表
     * $table 为必传参
     *
     * table 表名，where where条件，orderBy 排序，limit 限制
     * @param array|null $params
     * @return array
     */
    public function findAll($params) {
        $table = new TableModel();
        try {
            unset($params['searchName']);//必须传的3个参，不需要
            unset($params['page']);
            unset($params['limit']);
            if (!empty($params)) {
                $where = $params;
            }
            $where['delete_time is null'] = null;//没有被删除
            $where['status'] = 1;//状态正常
            $fields = 'id,username';
            $res = $table->tableList($this->tableName, $where, $fields);
            if (empty($res)) {
                return [
                    'status' => 200,
                    'data' => [],
                ];
            }
            foreach ($res as $k => $v) {
                //循环获取 角色 名称
                $groupAccessRes = $table->tableList('admin_auth_group_access', ['uid'=>$v['id']], 'group_id');
                if (count($groupAccessRes) == 1) {
                    $groupRes = $table->tableSingle('admin_auth_group', ['id'=>$groupAccessRes[0]['group_id']], 'title');
                    $res[$k]['title'] = $groupRes['title'];
                } else {
                    $res[$k]['title'] = '空';
                }
            }
            return [
                'status' => 200,
                'message' => '请求成功',
                'data' => $res,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => '数据库操作失败',
            ];
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
            if (!empty($params)) {
                $where = $params;
            }
            $where['delete_time is null'] = null;//没有被删除
            $where['status'] = 1;//状态正常
            $res = $table->tableSingle($this->tableName, $where);
            if (empty($res)) {
                return [
                    'status' => 200,
                    'message' => '请求成功',
                    'data' => [],
                ];
            }
            return [
                'status' => 200,
                'message' => '请求成功',
                'data' => $res,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => '数据库操作失败',
            ];
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
        $save =[
            'username' => $params['username'],
            'password' => md5($params['password'] . $params['salt']),
            'salt' => $params['salt'],
            'status' => 1,
            'create_time' => time()
        ];
        $table = new TableModel();
        $res = $table->tableAdd($this->tableName, $save);
        return [
            'status' => '200',
            'message' => '请求成功',
            'data' => $res
        ];
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
        $table = new TableModel();;
        $table->tableUpdate($this->tableName, ['delete_time'=>time()], ['id'=>$params['id']]);//软删除，只更新 删除时间 字段
        return [
            'status' => '200',
            'message' => '请求成功',
        ];
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1002 删除失败"}
    }

    /**
     * @param array|null $params
     * @return array
     */
    public function update($params)
    {
        $where = ['id'=>$params['id']];
        $table = new TableModel();
        unset($params['id']);
        try {
            $where['delete_time is null'] = null;//没有被删除
            $where['status'] = 1;//状态正常
            $table->tableUpdate($this->tableName, $params, $where);//软删除，只更新 删除时间 字段
            return [
                'status' => '200',
                'message' => '请求成功',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => '数据库操作失败',
            ];
        }
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1003 更新失败"}
    }

}
