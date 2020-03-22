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
class PostModel extends TableModel {

    public $table = "forum_post";

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
            $params['table'] = $this->table;
            $params['status'] = 1;
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
            $app[$i]['create_time'] = date('Y-m-d', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d', $app[$i]['update_time']);
            }
            if ($app[$i]['pic_urls'] != "") {
                $app[$i]['pic_urls'] = explode(",", $app[$i]['pic_urls']);
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

        try {
            //数据库操作
            if (isset($params['page'])) {
                if ($params['page'] < 1) {
                    $params['page'] = 1;
                }
                $num = 10;
                $page = $params['page'];
                $limit = ' limit ' . ($page - 1) * $num . "," . $num;
            }
            $str = "";
            if (isset($params['keywords_id'])) {
                if ($params['keywords_id']) {
                    $str = " and fp.keywords_id = " . $params['keywords_id'];
                } else {
                    unset($params['keywords_id']);
                }
                unset($params['keywords_id']);
            }
            if (isset($params['str'])) {
                if ($params['str']) {
                    $str = $str . " and (fp.content  like '%" . $params['str'] . "%' or  fp.title  like '%" . $params['str'] . "%')  ";
                } else {
                    unset($params['str']);
                }
                unset($params['str']);
            }
            $table = new TableModel();
            if (isset($params['user_id'])) {
                $sql = "select fp.*,fu.nickname,fu.avatar,fu.sex,fk.name as keyowrs_name,fu.score  from forum_post as fp INNER JOIN forum_user as fu on fu.id=fp.user_id left join forum_keywords as fk on fk.id =fp.keywords_id   where fp.delete_time is null and fp.status =1 and fp.`key` ='{$params['`key`']}' and fp.merchant_id = {$params['merchant_id']} and fp.user_id = {$params['user_id']} and fp.delete_time is null {$str} order by fp.comment_time desc,fp.id desc" . $limit;
            } else {
                $sql = "select fp.*,fu.nickname,fu.avatar,fu.sex,fk.name as keyowrs_name,fu.score  from forum_post as fp INNER JOIN forum_user as fu on fu.id=fp.user_id  left join forum_keywords as fk on fk.id =fp.keywords_id  where fp.delete_time is null and fp.status =1 and fp.`key` ='{$params['`key`']}' and fp.merchant_id = {$params['merchant_id']} and fp.delete_time is null {$str} order by fp.comment_time desc,fp.id desc" . $limit;
            }
            $app = $table->querySql($sql);
            if (isset($params['forum_user_collection.user_id'])) {
                $sql = "select * from forum_user_collection where user_id = " . $params['forum_user_collection.user_id'] . " and status =1";
                $collection = $table->querySql($sql);
                $sql = "select * from forum_user_like where user_id = " . $params['forum_user_collection.user_id'] . " and status =1";
                $like = $table->querySql($sql);
            }

            $sql = "select forum_user_like.source_id,forum_user.avatar as avatar from forum_user_like inner join forum_user on forum_user.id = forum_user_like.user_id where forum_user.status =1 and forum_user_like.status = 1 and forum_user_like.`key`='{$params['`key`']}' and forum_user_like.merchant_id = {$params['merchant_id']} order by forum_user_like.id desc ";
            $likes = $table->querySql($sql);

            $sql = "select forum_comment.content,forum_comment.post_id,forum_user.nickname as nickname   from forum_comment inner join forum_user on forum_user.id = forum_comment.user_id where forum_user.status =1 and forum_comment.status = 1  and forum_comment.`key`='{$params['`key`']}' and forum_comment.merchant_id = {$params['merchant_id']} order by forum_comment.id desc ";
            $comment = $table->querySql($sql);

            $sql = "select name,max_score,min_score from forum_user_level where `key`='{$params['`key`']}' and merchant_id = {$params['merchant_id']}";
            $levels = $table->querySql($sql);
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
            $app[$i]['collection'] = 0;
            $app[$i]['islike'] = 0;

            $content = preg_replace('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', "", $app[$i]['content']);
            $pics = html2imgs($app[$i]['content']);
            if (count($pics) != 0) {

                $app[$i]['pic_urls'] = $pics;
            }
            $app[$i]['content'] = $content;

            for ($j = 0; $j < count($levels); $j++) {
                if ($app[$i]['score'] <= $levels[$j]['max_score'] && $app[$i]['score'] >= $levels[$j]['min_score']) {
                    $app[$i]['level'] = $levels[$j]['name'];
                }
            }
        }

        if (isset($params['forum_user_collection.user_id'])) {
            for ($i = 0; $i < count($app); $i++) {
                for ($j = 0; $j < count($collection); $j++) {

                    if ($app[$i]['id'] == $collection[$j]['post_id']) {
                        $app[$i]['collection'] = 1;
                    }
                }
                for ($k = 0; $k < count($like); $k++) {
                    if ($app[$i]['id'] == $like[$k]['source_id']) {
                        $app[$i]['islike'] = 1;
                    }
                }
                $com = array();
                for ($r = 0; $r < count($comment); $r++) {
                    if ($app[$i]['id'] == $comment[$r]['post_id']) {
                        $com['content'] = $comment[$r]['content'];
                        $com['nickname'] = $comment[$r]['nickname'];                       
                        $app[$i]['comment'][] = $com;
                        if (count($app[$i]['comment']) >= 3) {
                            break;
                        }
                    }
                }

                for ($t = 0; $t < count($likes); $t++) {
                    if ($app[$i]['id'] == $likes[$t]['source_id']) {
                        $app[$i]['like_user'][] = $likes[$t]['avatar'];
                        if (count($app[$i]['like_user']) >= 14) {
                            break;
                        }
                    }
                }
            }
        } else {
            for ($i = 0; $i < count($app); $i++) {
                $com = array();
                for ($r = 0; $r < count($comment); $r++) {
                    if ($app[$i]['id'] == $comment[$r]['post_id']) {
                        $com['content'] = $comment[$r]['content'];
                        $com['nickname'] = $comment[$r]['nickname'];

                        $app[$i]['comment'][] = $com;
                        if (count($app[$i]['comment']) >= 3) {
                            break;
                        }
                    }
                }
                for ($t = 0; $t < count($likes); $t++) {
                    if ($app[$i]['id'] == $likes[$t]['source_id']) {
                        $app[$i]['like_user'][] = $likes[$t]['avatar'];
                        if (count($app[$i]['like_user']) >= 14) {
                            break;
                        }
                    }
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
        //数据库操作
        try {
            $table = new TableModel();
            $sql = "select fp.*,fu.nickname,fu.avatar,fu.sex,fyl.`status` as islike from forum_post as fp INNER JOIN forum_user as fu on fu.id=fp.user_id  left join forum_user_like as fyl on fyl.source_id = fp.id where fp.id = {$params['id']} and fp.delete_time is null and fp.status =1 and fp.`key` ='{$params['`key`']}' and fp.merchant_id = {$params['merchant_id']}";
            $app = $table->querySql($sql);
            if (!$app) {
                return result(204, '查询失败');
            }
            $app = $app[0];
            //点赞列表
            $sql = "select fu.nickname,fu.avatar,fu.sex,fp.address,fp.content,fp.title,fp.voice_url from forum_user_like as ful INNER JOIN forum_user as fu on fu.id=ful.user_id  inner join forum_post as fp on fp.id = ful.source_id where  ful.delete_time is null and ful.`key` ='{$params['`key`']}' and ful.merchant_id = {$params['merchant_id']} and ful.source_id = {$params['id']} and ful.`status` =1 order by ful.id desc";
            $likes = $table->querySql($sql);
            $app['likes'] = $likes;
            //回帖列表
            $sql = "select fc.*,fu.nickname,fu.avatar,fu.sex from forum_comment as fc INNER JOIN forum_user as fu on fu.id=fc.user_id   where fc.delete_time is null and fc.status =1 and fc.`key` ='{$params['`key`']}' and fc.merchant_id = {$params['merchant_id']} and fc.post_id = {$params['id']} and fc.comment_id = 0 order by fc.id desc";
            $comments = $table->querySql($sql);

            $ids = "";
            if ($comments) {
                for ($i = 0; $i < count($comments); $i++) {
                    $ids .= $comments[$i]['id'] . ",";
                }
                $ids = substr($ids, 0, strlen($ids) - 1);
                //回帖的回帖列表
                $sql = "select fc.*,fu.nickname,fu.avatar,fu.sex from forum_comment as fc INNER JOIN forum_user as fu on fu.id=fc.user_id   where fc.delete_time is null and fc.status =1 and fc.`key` ='{$params['`key`']}' and fc.merchant_id = {$params['merchant_id']} and fc.comment_id in ({$ids}) order by fc.id desc";
                $b = $table->querySql($sql);


                $count1 = count($comments);
                $count2 = count($b);
                for ($j = 0; $j < $count1; $j++) {
                    $array = array();
                    if ($comments[$j]['pic_urls'] != "") {
                        $comments[$j]['pic_urls'] = explode(",", $comments[$j]['pic_urls']);
                        if (is_array($comments[$j]['pic_urls'])) {
                            for ($y = 0; $y < count($comments[$j]['pic_urls']); $y++) {
                                $comments[$j]['pic_urls'][$y] = $comments[$j]['pic_urls'][$y];
                            }
                        }
                    }
                    $comments[$j]['create_time'] = date('Y-m-d', $comments[$j]['create_time']);
                    if ($b) {
                        for ($k = 0; $k < $count2; $k++) {
                            if ($comments[$j]['id'] == $b[$k]['comment_id']) {
                                if ($b[$k]['pic_urls'] != "") {
                                    $b[$k]['pic_urls'] = explode(",", $b[$k]['pic_urls']);
                                    if (is_array($b[$k]['pic_urls'])) {
                                        for ($t = 0; $t < count($b[$k]['pic_urls']); $t++) {
                                            $b[$k]['pic_urls'][$t] = $b[$k]['pic_urls'][$t];
                                        }
                                    }
                                }
                                $b[$k]['create_time'] = date('Y-m-d', $b[$k]['create_time']);
                                $array[] = $b[$k];
                            }
                        }
                        $comments[$j]['comments'] = $array;
                    }
                }
                $app['comments'] = $comments;
            }
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
        } else {
            $app['create_time'] = date('Y-m-d', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d', $app['update_time']);
            }
            if ($app['pic_urls'] != "") {
                $app['pic_urls'] = explode(",", $app['pic_urls']);
                for ($j = 0; $j < count($app['pic_urls']); $j++) {
                    $app['pic_urls'][$j] = $app['pic_urls'][$j];
                }
            }

            return result(200, '请求成功', $app);
        }
    }

    public function one($params) {

        $table = new TableModel();
        //数据库操作
        try {
            $sql = "select fp.*,fu.nickname,fu.avatar,fu.sex,fyl.`status` as islike from forum_post as fp INNER JOIN forum_user as fu on fu.id=fp.user_id  left join forum_user_like as fyl on fyl.source_id = fp.id where fp.id = {$params['id']} and fp.delete_time is null and fp.status =1 and fp.`key` ='{$params['`key`']}' and fp.merchant_id = {$params['merchant_id']}";
            $app = $table->querySql($sql);
            $app = $app[0];
            //点赞列表
            $sql = "select fu.nickname,fu.avatar,fu.sex,fp.address,fp.pic_urls,fp.content,fp.title,fp.voice_url from forum_user_like as ful INNER JOIN forum_user as fu on fu.id=ful.user_id  inner join forum_post as fp on fp.id = ful.source_id where  ful.delete_time is null and ful.`key` ='{$params['`key`']}' and ful.merchant_id = {$params['merchant_id']} and ful.source_id = {$params['id']} and ful.`status` =1 order by ful.id desc";
            $likes = $table->querySql($sql);
            $app['likes'] = $likes;
            //回帖列表
            $sql = "select fc.*,fu.nickname,fu.avatar,fu.sex from forum_comment as fc INNER JOIN forum_user as fu on fu.id=fc.user_id   where fc.delete_time is null and fc.status =1 and fc.`key` ='{$params['`key`']}' and fc.merchant_id = {$params['merchant_id']} and fc.post_id = {$params['id']} and fc.comment_id = 0 order by fc.id desc";
            $comments = $table->querySql($sql);

            $ids = "";
            if ($comments) {
                for ($i = 0; $i < count($comments); $i++) {
                    $ids .= $comments[$i]['id'] . ",";
                }
                $ids = substr($ids, 0, strlen($ids) - 1);
                //回帖的回帖列表
                $sql = "select fc.*,fu.nickname,fu.avatar,fu.sex from forum_comment as fc INNER JOIN forum_user as fu on fu.id=fc.user_id   where fc.delete_time is null and fc.status =1 and fc.`key` ='{$params['`key`']}' and fc.merchant_id = {$params['merchant_id']} and fc.comment_id in ({$ids}) order by fc.id desc";
                $b = $table->querySql($sql);
                $array = array();
                if ($b) {
                    $count1 = count($comments);
                    $count2 = count($b);
                    for ($j = 0; $j < $count1; $j++) {
                        $comments[$j]['pic_urls'] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $comments[$j]['pic_urls'];
                        $comments[$j]['create_time'] = date('Y-m-d', $comments[$j]['create_time']);
                        for ($k = 0; $k < $count2; $k++) {
                            if ($comments[$j]['id'] == $b[$k]['comment_id']) {
                                $b[$k]['pic_urls'] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $b[$k]['pic_urls'];
                                $b[$k]['create_time'] = date('Y-m-d', $b[$k]['create_time']);
                                $array[] = $b[$k];
                            }
                        }
                        $comments[$j]['comments'] = $array;
                    }
                }
                $app['comments'] = $comments;
            }
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '查询失败');
        } else {
            $app['create_time'] = date('Y-m-d', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d', $app['update_time']);
            }
            if ($app['pic_urls'] != "") {
                $app['pic_urls'] = explode(",", $app['pic_urls']);
                for ($j = 0; $j < count($app['pic_urls']); $j++) {
                    $app['pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app['pic_urls'][$j];
                }
            }

            return result(200, '请求成功', $app);
        }
    }

    public function onlyOne($params) {

        $table = new TableModel();
        //数据库操作
        try {
            $app = $table->tableSingle('forum_post', ['id' => $params['id'], 'delete_time is null' => null, 'status' => 1]);
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

    public function postTop($params) {
        try {
            $table = new TableModel();
            $params['delete_time is null'] = null;
            $params['table'] = $this->table;
            $res = $table->tableList($params);
            $app = $res['app'];
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
            $params['comment_time'] = time();
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
        $where = ['id' => $params['id'], '`key`' => $params['`key`'], 'merchant_id' => $params['merchant_id'], 'user_id' => $params['user_id']];
        //params 参数设置
        unset($params['id']);
        unset($params['`key`']);
        unset($params['merchant_id']);
        unset($params['user_id']);
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

        //数据库操作
        if (isset($params['id'])) {
            $where['id'] = $params['id'];
            unset($params['id']);
        }
        if (isset($params['`key`'])) {
            $where['`key`'] = $params['`key`'];
            unset($params['`key`']);
        }
        if (isset($params['id'])) {
            $where['merchant_id'] = $params['merchant_id'];
            unset($params['merchant_id']);
        }
        if (isset($params['id'])) {
            $where['user_id'] = $params['user_id'];
            unset($params['user_id']);
        }
        $where['delete_time is null'] = null;
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

    /**
     * 更新接口
     * 地址:/admin/group/update
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function updatePost($params) {

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
