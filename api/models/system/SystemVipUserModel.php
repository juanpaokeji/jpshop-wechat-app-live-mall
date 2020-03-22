<?php

/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace app\models\system;

//引入各表实体
use yii\db\Exception;
use app\models\common\CommonModel;

/**
 *
 * @version   2018年04月16日
 * @author    YangJing <120912212@qq.com>
 * @copyright Copyright 2018 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 *
 * @Bean()
 */
class SystemVipUserModel extends CommonModel {

//    /**
//     * @return string
//     */
    public static function tableName() {
        return 'system_vip_user';
    }

    /**
     * @return array
     */
    public function attributeLabels() {
        return [];
    }

    /**
     * 查询列表接口
     * 地址:/admin/group/list
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function do_select($params) {
        //数据库操作      
        $params['orderby'] = "id desc";
        $params['count'] = true;
        $res = $this->get_list($params);
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return ['status' => 200, 'message' => '请求成功', 'data' => $res['data'], 'count' => $res['count']];
        }
    }

    public function do_one($params) {

        $res = $this->get_info($params);

        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return result(200, '请求成功', $res);
        }
    }

    public function do_add($params) {
        $data = array();
        $res = $this->modify($params, $data);
        if (empty($res)) {
            return result(204, '查询失败');
        } else {
            return result(200, '请求成功', $res);
        }
    }

    public function do_update($where, $data) {

        $res = $this->modify($where, $data);
        if ($res == false) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

    public function do_delete($where) {
        $res = $this->soft_delete($where);
        if ($res == false) {
            return result(500, '删除失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 累加记录
     */
    public function do_Inc($where = [], $field = '', $offset = 1) {
        $res = $this->setInc($where = [], $field = '', $offset = 1);
        if ($res == false) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

    /**
     * 累减记录
     */
    public function do_Dec($where = [], $field = '', $offset = 1) {
        $res = $this->setDec($where = [], $field = '', $offset = 1);
        if ($res == false) {
            return result(500, '更新失败');
        } else {
            return result(200, '请求成功');
        }
    }

}
