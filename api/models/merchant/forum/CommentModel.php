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
use app\models\merchant\user\UserModel;
use app\models\merchant\forum\PostModel;

/**
 *
 * @version   2018年04月16日
 * @author    YangJing <120912212@qq.com>
 * @copyright Copyright 2018 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class CommentModel extends TableModel {

    public $table = "forum_comment";

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
            $params['forum_comment.delete_time is null'] = null;
            $params['table'] = $this->table;
            $params['fields'] = "forum_comment.id,forum_comment.`key`,forum_comment.merchant_id,forum_comment.user_id,forum_comment.post_id,forum_comment.comment_id,forum_comment.content,forum_comment.address,forum_comment.pic_urls,forum_comment.voice_url,forum_comment.like_count,forum_comment.`status`,forum_comment.create_time,forum_comment.update_time,forum_comment.delete_time,forum_user.nickname as nick_name ";
            $params['join'] = " INNER JOIN forum_user on forum_user.id = forum_comment.user_id ";
            $params['orderby'] = " forum_comment.id DESC";
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["forum_comment.content like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }
            if (isset($params['searchUserName'])) {
                $params['searchUserName'] = trim($params['searchUserName']);
                $params["nickname like '%{$params['searchUserName']}%'"] = null;
                unset($params['searchUserName']);
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
            if ($app[$i]['pic_urls']) {
                $app[$i]['pic_urls'] = explode(",", $app[$i]['pic_urls']);
                for ($j = 0; $j < count($app[$i]['pic_urls']); $j++) {
                    $app[$i]['pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app[$i]['pic_urls'][$j];
                }
            }

            $postModel = new PostModel();
            $postData['id'] = $app[$i]['post_id'];
            $posts = $postModel->find($postData);
            if ($posts['status'] == 200) {
                $app[$i]['post_content'] = $posts['data']['content'];
            } else {
                $app[$i]['post_content'] = "";
            }
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app, 'count' => $res['count']];
        }
    }

//    public function finds($params) {
//        //数据库操作
//        try {
//            if (isset($params['page'])) {
//                if ($params['page'] < 1) {
//                    $params['page'] = 1;
//                }
//                $num = 10;
//                $page = $params['page'];
//                $limit = ' limit ' . ($page - 1) * $num . "," . $num;
//            }
//            $table = new TableModel();
//            $sql = "select fc.*,fp.title,fu.nickname,fu.avatar,fu.sex from forum_comment as fc INNER JOIN forum_user as fu on fu.id=fc.user_id  INNER JOIN forum_post as fp on fp.id = fc.post_id  where fc.delete_time is null and fc.status =1 and fc.`key` ='{$params['`key`']}' and fc.merchant_id = {$params['merchant_id']}  order by fc.id desc" . $limit;
//            $app = $table->querySql($sql);
//        } catch (Exception $ex) {
//            return result(500, '数据库操作失败');
//        }
//        //返回数据 时间格式重置
//        for ($i = 0; $i < count($app); $i++) {
//            $app[$i]['create_time'] = date('Y-m-d', $app[$i]['create_time']);
//            if ($app[$i]['update_time'] != "") {
//                $app[$i]['update_time'] = date('Y-m-d', $app[$i]['update_time']);
//            }
//            $app[$i]['pic_urls'] = explode(",", $app[$i]['pic_urls']);
//            for ($j = 0; $j < count($app[$i]['pic_urls']); $j++) {
//                $app[$i]['pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app[$i]['pic_urls'][$j];
//            }
//        }
//        if (empty($app)) {
//            return result(204, '查询失败');
//        } else {
//            return ['status' => 200, 'message' => '请求成功', 'data' => $app];
//        }
//    }

    public function onlyOne($params) {

        $table = new TableModel();
        //数据库操作
        try {
            $app = $table->tableSingle($this->table, ['id' => $params['id'], 'delete_time is null' => null, 'status' => 1]);
        } catch (Exception $ex) {
            return json_encode(['status' => '500', 'message' => '数据库操作失败',], JSON_UNESCAPED_UNICODE);
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
     * 查询单条接口
     * 地址:/admin/group/single
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function find($params) {

        $table = new TableModel();
        //数据库操作
        try {
            $app = $table->tableSingle($this->table, ['id' => $params['id'], 'delete_time is null' => null]);
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
            if ($app['pic_urls']) {
                $app['pic_urls'] = explode(",", $app['pic_urls']);
                for ($j = 0; $j < count($app['pic_urls']); $j++) {
                    $app['pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app['pic_urls'][$j];
                }
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
        $where = $params;
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
        $where = ['id' => $params['id']];
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

    /**
     * 更新接口
     * 地址:/admin/group/update
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function updateComment($params) {
        try {
            $where = ['id' => $params['id']];
            $where['delete_time is null'] = null;
            unset($params['id']);
            //数据库操作
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

}
