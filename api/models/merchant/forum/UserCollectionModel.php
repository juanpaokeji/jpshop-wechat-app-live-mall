<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\merchant\forum;

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
class UserCollectionModel extends TableModel {

    public $table = "forum_user_collection";

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
        $params['table'] = $this->table;
        if (isset($params['searchName'])) {
            $params['searchName'] = trim($params['searchName']);
            $params["name like '%{$params['searchName']}%'"] = null;
            unset($params['searchName']);
        }
        $res = $table->tableList($params);
        $app = $res['app'];
        try {
            
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
     * 查询列表接口
     * 地址:/admin/group/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function finds($params) {
        //数据库操作

        try {
            if (isset($params['page'])) {
                if ($params['page'] < 1) {
                    $params['page'] = 1;
                }
                $num = 10;
                $page = $params['page'];
                $limit = ' limit ' . ($page - 1) * $num . "," . $num;
            }
            $table = new TableModel();
            $sql = "select fuc.*,fu.nickname,fu.avatar,fu.sex,fp.address,fp.pic_urls,fp.content,fp.title,fp.voice_url from forum_user_collection as fuc INNER JOIN forum_user as fu on fu.id=fuc.user_id  inner join forum_post as fp on fp.id = fuc.post_id where  fuc.delete_time is null and fuc.`key` ='{$params['`key`']}' and fuc.merchant_id = {$params['merchant_id']} and fuc.user_id = {$params['user_id']} and fuc.`status` =1 order by fuc.id desc" . $limit;
            $app = $table->querySql($sql);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d', $app[$i]['update_time']);
            }
            if ($app[$i]['pic_urls'] != "") {
                $app[$i]['pic_urls'] = explode(",", $app[$i]['pic_urls']);
                for ($j = 0; $j < count($app[$i]['pic_urls']); $j++) {
                    $app[$i]['pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app[$i]['pic_urls'][$j];
                }
            }
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app];
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
        $where['post_id'] = $params['post_id'];
        $where['`key`'] = $params['`key`'];
        $where['merchant_id'] = $params['merchant_id'];
        $where['user_id'] = $params['user_id'];
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
        $table = new TableModel();
        $params['create_time'] = time();
        $res = $table->tableAdd($this->table, $params);
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
        $where = ['post_id' => $params['post_id']];
        $where['`key`'] = $params['`key`'];
        $where['merchant_id'] = $params['merchant_id'];
        $where['user_id'] = $params['user_id'];
        $where['delete_time is null'] = null;
        unset($params['merchant_id']);
        unset($params['`key`']);
        unset($params['user_id']);
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

}
