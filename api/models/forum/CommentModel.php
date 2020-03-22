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
            if (isset($params['page'])) {
                if ($params['page'] < 1) {
                    $params['page'] = 1;
                }
                $num = 10;
                $page = $params['page'];
                $limit = ' limit ' . ($page - 1) * $num . "," . $num;
            }
            $table = new TableModel();
            $sql = "select fc.*,fp.title,fu.nickname,fu.avatar,fu1.sex,fu1.nickname as post_nickname,fp.content AS post_content,fp.pic_urls AS post_pic_urls,fu1.avatar AS post_avatar,	fk. NAME AS keywords from forum_comment as fc INNER JOIN forum_user as fu on fu.id=fc.user_id  INNER JOIN forum_post as fp on fp.id = fc.post_id LEFT JOIN  forum_user as fu1 on fu1.id = fp.user_id LEFT JOIN forum_keywords AS fk on fk.id = fp.keywords_id  where fc.delete_time is null and fc.status =1 and fc.`key` ='{$params['`key`']}' and fc.merchant_id = {$params['merchant_id']} and fc.user_id = {$params['user_id']}  order by fc.id desc" . $limit;
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
            if ($app[$i]['pic_urls']) {
                $app[$i]['pic_urls'] = explode(",", $app[$i]['pic_urls']);
//                for ($j = 0; $j < count($app[$i]['pic_urls']); $j++) {
//                    $app[$i]['pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app[$i]['pic_urls'][$j];
//                }
            } else {
                $app[$i]['pic_urls'] = array();
            }
            if ($app[$i]['post_pic_urls']) {
                $app[$i]['post_pic_urls'] = explode(",", $app[$i]['post_pic_urls']);
//                for ($j = 0; $j < count($app[$i]['post_pic_urls']); $j++) {
//                    $app[$i]['post_pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app[$i]['post_pic_urls'][$j];
//                }
            } else {
                $app[$i]['post_pic_urls'] = array();
            }
            $content = preg_replace('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', "", $app[$i]['content']);
            $pics = html2imgs($app[$i]['content']);
            if (count($pics) != 0) {

                $app[$i]['pic_urls'] = $pics;
            }
            $app[$i]['content'] = $content;


            $content = preg_replace('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', "", $app[$i]['post_content']);
            $pics = html2imgs($app[$i]['post_content']);
            if (count($pics) != 0) {
                $app[$i]['post_pic_urls'] = $pics;
            }
            $app[$i]['post_content'] = $content;
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app];
        }
    }

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
            $sql = "select fc.*,fp.title,fu.nickname,fu.avatar,fu.sex from forum_comment as fc INNER JOIN forum_user as fu on fu.id=fc.user_id  INNER JOIN forum_post as fp on fp.id = fc.post_id  where fc.delete_time is null and fc.status =1 and fc.`key` ='{$params['`key`']}' and fc.merchant_id = {$params['merchant_id']}  order by fc.id desc" . $limit;
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
            } else {
                $app[$i]['pic_urls'] = array();
            }
            $content = preg_replace('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', "", $app[$i]['content']);
            $pics = html2imgs($app[$i]['content']);
            if (count($pics) != 0) {

                $app[$i]['pic_urls'] = $pics;
            }
            $app[$i]['content'] = $content;
        }
        if (empty($app)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $app];
        }
    }

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
