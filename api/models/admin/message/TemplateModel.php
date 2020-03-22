<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/17 13:06
 */

namespace app\models\admin\message;

use yii\db\Exception;
use app\models\core\TableModel;

/**
 * 通用表格操作 model
 */
class TemplateModel extends TableModel {
    public $tableName = 'system_sms_template';

    /**
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
     * @param array|null $params
     * @return array
     */
    public function add($params)
    {
        try {
            $table = new TableModel();
            $res = $table->tableAdd($this->tableName, $params);
        }catch (Exception $e) {
            return [
                'status' => 500,
                'message' => '添加失败',
            ];
        }
        return [
            'status' => 200,
            'message' => '请求成功',
            'data' => $res
        ];
        //请求成功示例 {"status":"200","message":"请求成功","data":4}
        //请求失败示例 {"status":"500","message":"1001 添加失败"}
    }

    /**
     * @param array|null $params
     * @return array
     */
    public function delete($params, $uid = 0)
    {
        $where['qcloud_template_id'] = $params['id'];
        if ($uid != 0) {
            $where['merchant_id'] = $uid;
        }
        $table = new TableModel();
        try {
            $rs = $table->tableUpdate($this->tableName, ['delete_time'=>time()], $where);//软删除，只更新 删除时间 字段
            if ($rs == 0) {
                return false;
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => '删除失败',
            ];
        }
        return [
            'status' => 200,
            'message' => '请求成功',
        ];
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1002 删除失败"}
    }

    /**
     * @param array|null $params
     * @return array
     */
    public function update($params, $uid = 0)
    {
        $where['qcloud_template_id'] = $params['id'];
        if ($uid != 0) {
            $where['merchant_id'] = $uid;
        }
        unset($params['id']);
        $table = new TableModel();
        try {
            $rs = $table->tableUpdate($this->tableName, $params, $where);//软删除，只更新 删除时间 字段
            if ($rs == 0) {
                return false;
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => '1003 更新失败',
            ];
        }
        return [
            'status' => 200,
            'message' => '请求成功',
        ];
        //请求成功示例 {"status":"200","message":"请求成功"}
        //请求失败示例 {"status":"500","message":"1003 更新失败"}
    }

}