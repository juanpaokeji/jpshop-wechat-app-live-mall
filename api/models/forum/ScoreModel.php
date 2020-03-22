<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\forum;

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
class ScoreModel extends TableModel {

    public $table = "forum_user_score";

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
            $params['table'] = $this->table;
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
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d H:i:s', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d H:i:s', $app[$i]['update_time']);
            }
        }
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
    public function find($params) {

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
        try {
            $app = $table->tableSingle($this->table, $where);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
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
    public function delete($params) {
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
    public function update($params) {
        //where 条件设置
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            $where['delete_time is null'] = null;
        }
        if (isset($params['merchant_id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            $where['delete_time is null'] = null;
        }
        unset($params['id']);
        //params 参数值设置
        $params['update_time'] = time();
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
            return result(200, '请求成功');
        }
    }

    //$params 社交用户积分表信息   $type类型 post=发帖 replies=回帖 sign_in=登陆 forward=转发 collection=收藏     $scores 商户积分设置
    public function score($params, $type, $scores) {


        $table = new TableModel();
        $time = date("Y-m-d");
        $startTime = date('Y-m-d H:i:s', strtotime($time . " 00:00:00"));
        $endTime = date('Y-m-d H:i:s', strtotime($time . " 23:59:59"));

        $sql = "select sum(score)as num from forum_user_score where from_unixtime(create_time) >'{$startTime}' and  from_unixtime(create_time)<'{$endTime}'  and user_id = '{$params['user_id']}' and merchant_id = '{$params['merchant_id']}' and `key` = '{$params['`key`']}'";
        $res = $table->querySql($sql);
        $score = 0;
        //"post":"3","replies":"2","sign_in":"5","forward":"2","collection":"2"
        $bool = true;
        if ($type == "post") {
            $score = $scores['post'];
        } else if ($type == "replies") {
            $score = $scores['replies'];
        } else if ($type == "sign_in") {
            $score = $scores['sign_in'];
            $sql = "select *  from forum_user_score where from_unixtime(create_time) >'{$startTime}' and  from_unixtime(create_time)<'{$endTime}'  and user_id = '{$params['user_id']}' and merchant_id = '{$params['merchant_id']}' and `key` = '{$params['`key`']}' and type = 'sign_in'";
            $resSign = $table->querySql($sql);
            if (count($resSign) != 0) {
                $bool = false;
            }
        } else if ($type == "forward") {
            $score = $scores['forward'];
        } else if ($type == "collection") {
            $score = $scores['collection'];
        } else {
            return result(500, '新增失败');
        }

        $data['`key`'] = $params['`key`'];
        $data['merchant_id'] = $params['merchant_id'];
        $data['user_id'] = $params['user_id'];
        $data['source_id'] = $params['source_id'];
        // $data['content'] = $params['content'];
        $data['type'] = $params['type'];
        $data['status'] = 1;

        if ($scores['day_total'] > $res[0]['num'] + $score) {
            $data['score'] = $score;
        } else {
            $data['score'] = $scores['day_total'] - $res[0]['num'];
        }

        if ($data['score'] != 0 && $bool == true) {
            $data['create_time'] = time();
            //新增积分记录
            $res = $table->tableAdd($this->table, $data);
            $sql = "update forum_user set score = score+{$score} where id = '{$params['user_id']}' and merchant_id = '{$params['merchant_id']}' and `key` = '{$params['`key`']}'";
            $a = yii::$app->db->createCommand($sql)->execute();
        }

        try {
            
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$res) {
            return result(500, '新增失败');
        } else {
            return result(200, '请求成功', $res);
        }
    }

}
