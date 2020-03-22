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
class UserLikeModel extends TableModel {

    public $table = "forum_user_like";

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
            $params['status'] = 1;
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
            $sql = "select ful.*,fu.nickname,fu.avatar,fu.sex,fp.address,fp.pic_urls,fp.content,fp.title,fp.voice_url,fk. NAME AS keywords from forum_user_like as ful   inner join forum_post as fp on fp.id =  ful.source_id INNER JOIN forum_user as fu on fu.id=fp.user_id LEFT JOIN forum_keywords AS fk on fk.id = fp.keywords_id where  ful.delete_time is null and ful.`key` ='{$params['`key`']}' and ful.merchant_id = {$params['merchant_id']} and ful.user_id = {$params['user_id']} and ful.`status` =1 order by ful.id desc" . $limit;
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

            if ($app[$i]['pic_urls'] == "") {
                $app[$i]['pic_urls'] = array();
            } else {
                $app[$i]['pic_urls'] = explode(",", $app[$i]['pic_urls']);
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

    /**
     * 查询单条接口
     * 地址:/admin/group/single
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function find($params) {

        $table = new TableModel();
        //数据库操作
        $where['source_id'] = $params['source_id'];
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
        try {
            //数据库操作
            $table = new TableModel();
            $params['create_time'] = time();
            $data['source_id'] = $params['source_id'];
            $data['`key`'] = $params['`key`'];
            $data['merchant_id'] = $params['merchant_id'];
            $data['user_id'] = $params['user_id'];
            $single = $table->tableSingle($this->table, $data);
            if ($single['status'] == 200) {
                return result(500, '已点赞');
            } else {
                $res = $table->tableAdd($this->table, $params);
            }
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
        $where = ['source_id' => $params['source_id']];
        unset($params['source_id']);
        $where['`key`'] = $params['`key`'];
        $where['merchant_id'] = $params['merchant_id'];
        $where['user_id'] = $params['user_id'];
        $where['delete_time is null'] = null;
        unset($params['merchant_id']);
        unset($params['`key`']);
        unset($params['user_id']);
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

    public function likeCount($where) {
        $table = new TableModel();
        try {
            $sql = "select count(id) as num from forum_user_like  where source_id = '{$where['source_id']}' and `key` = '{$where['`key`']}' and merchant_id = '{$where['merchant_id']}' and status = '1'";
            $app = $table->querySql($sql);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (!$app) {
            return result(500, '查询失败');
        } else {
            return $app[0]['num'];
        }
    }

}
