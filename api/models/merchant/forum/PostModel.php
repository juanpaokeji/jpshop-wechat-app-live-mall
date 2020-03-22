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
use app\models\merchant\forum\KeyWordsModel;
use app\models\merchant\user\UserModel;

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
            $params['orderby'] = 'id desc ';
            $params['table'] = $this->table;
            if (isset($params['post'])) {
                if ($params['post'] == 0) {
                    
                }
                if ($params['post'] == "is_essence") {
                    $params['is_essence'] = 1;
                }
                if ($params['post'] == "is_top") {
                    $params['is_top'] = 1;
                }
                unset($params['post']);
            }

            if (isset($params['type'])) {
                if ($params['type'] == 0) {
                    unset($params['type']);
                }
            }
            if (isset($params['keywords_id'])) {
                if ($params['keywords_id'] == 0) {
                    unset($params['keywords_id']);
                }
            }

            if (isset($params['startTime'])) {
                if ($params['startTime']) {
                    $params['startTime'] = strtotime(str_replace("*", " ", $params['startTime']));
                    $params["create_time >={$params['startTime']}"] = null;
                }
                unset($params['startTime']);
            }
            if (isset($params['endTime'])) {
                if ($params['endTime']) {
                    $params['endTime'] = strtotime(str_replace("*", " ", $params['endTime']));
                    $params["create_time <={$params['endTime']}"] = null;
                }
                unset($params['endTime']);
            }
            if (isset($params['time_sort'])) {
                if ($params['time_sort'] == 0) {
                    $params['orderby'] = " create_time desc";
                }
                if ($params['time_sort'] == 1) {
                    $params['orderby'] = " comment_time desc";
                }
                unset($params['time_sort']);
            }
            if (isset($params['searchName'])) {
                $params['searchName'] = trim($params['searchName']);
                $params["content like '%{$params['searchName']}%'"] = null;
                unset($params['searchName']);
            }
            if (!isset($params['status'])) {
                $params[" status !=0 "] = null;
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
            $app[$i]['pic_urls'] = explode(",", $app[$i]['pic_urls']);
            for ($j = 0; $j < count($app[$i]['pic_urls']); $j++) {
                $app[$i]['pic_urls'][$j] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app[$i]['pic_urls'][$j];
            }
            $keyWords = new KeyWordsModel();
            $data['id'] = $app[$i]['keywords_id'];
            $rs = $keyWords->find($data);
            if ($rs['status'] == 200) {
                $app[$i]['keywords_id'] = $rs['data']['name'];
            } else {
                $app[$i]['keywords_id'] = "";
            }

            $userModel = new UserModel();
            $userData['id'] = $app[$i]['user_id'];
            $users = $userModel->find($userData);

            if ($users['status'] == 200) {
                $app[$i]['user_id'] = $users['data']['nickname'];
            } else {
                $app[$i]['user_id'] = "";
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
            if (isset($params['user_id'])) {
                $sql = "select fp.*,fu.nickname,fu.avatar,fu.sex,fyl.`status` as islike from forum_post as fp INNER JOIN forum_user as fu on fu.id=fp.user_id  left join forum_user_like as fyl on fyl.source_id = fp.id where fp.delete_time is null and fp.status =1 and fp.`key` ='{$params['`key`']}' and fp.merchant_id = {$params['merchant_id']} and fp.user_id = {$params['user_id']} order by fp.id desc" . $limit;
            } else {
                $sql = "select fp.*,fu.nickname,fu.avatar,fu.sex,fyl.`status` as islike from forum_post as fp INNER JOIN forum_user as fu on fu.id=fp.user_id  left join forum_user_like as fyl on fyl.source_id = fp.id where fp.delete_time is null and fp.status =1 and fp.`key` ='{$params['`key`']}' and fp.merchant_id = {$params['merchant_id']} order by fp.id desc" . $limit;
            }
            $app = $table->querySql($sql);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        //返回数据 时间格式重置
        for ($i = 0; $i < count($app); $i++) {
            $app[$i]['create_time'] = date('Y-m-d  H:i:s', $app[$i]['create_time']);
            if ($app[$i]['update_time'] != "") {
                $app[$i]['update_time'] = date('Y-m-d  H:i:s', $app[$i]['update_time']);
            }
            $app[$i]['voice_url'] = "http://juanpao999-1255754174.cos.cn-south.myqcloud.com/forum/" . $app[$i]['voice_url'];
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
        try {
            $app = $table->tableSingle($this->table, ['id' => $params['id'], 'delete_time is null' => null]);
        } catch (Exception $ex) {
            return result(500, '数据库操作失败');
        }
        if (gettype($app) != 'array') {
            return result(204, '未找到对应数据');
        } else {
            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
            if ($app['update_time'] != "") {
                $app['update_time'] = date('Y-m-d H:i:s', $app['update_time']);
            }
            $keyWords = new KeyWordsModel();
            $data['id'] = $app['keywords_id'];
            $rs = $keyWords->find($data);
            if ($rs['status'] == 200) {
                $app['keywords'] = $rs['data']['name'];
            } else {
                $app['keywords'] = "";
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
            $params['status'] = 1;
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
        $where = ['id' => $params['id'], '`key`' => $params['`key`'], 'merchant_id' => $params['merchant_id']];
        //params 参数设置
        unset($params['id']);
        unset($params['`key`']);
        unset($params['merchant_id']);
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

        try {
            $where = ['id' => $params['id']];
            $where['`key`'] = $params['`key`'];
            $where['merchant_id'] = $params['merchant_id'];
            $where['delete_time is null'] = null;
            unset($params['merchant_id']);
            unset($params['`key`']);
            unset($params['id']);
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
    public function updateMore($params) {


        try {
            //数据库操作
            $where = ["id in ({$params['id']})" => null];
            $where['`key`'] = $params['`key`'];
            $where['merchant_id'] = $params['merchant_id'];
            $where['delete_time is null'] = null;
            unset($params['merchant_id']);
            unset($params['`key`']);
            unset($params['id']);
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
