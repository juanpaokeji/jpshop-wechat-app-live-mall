<?php

namespace app\models\core;

use app\models\core\TableModel;
use yii\db\Exception;

class WxConfigModel
{
    /**
     * @param int $id 商户id
     * @return array
     */
    public function getConfig($id)
    {
        $table = new TableModel();
        $where = [
            'id' => $id,
            'delete_time is null' => NULL,//没有被删除
            'status' => 1,//状态正常
        ];
        $fields = 'config';
        try {
            $res = $table->tableSingle('merchant_user', $where, $fields);
            if (!$res || empty($res) || is_null($res['config'])) {
                $array = [
                    'status' => 404,
                    'message' => '该商户配置不存在',
                ];
                return $array;
            }
            $config = json_decode($res['config']);
            if (!isset($config->weixin_pay) || empty($config->weixin_pay->APPID)) {
                $array = [
                    'status' => 404,
                    'message' => '商户微信配置不存在',
                ];
                return $array;
            }
            $return = [
                'status' => 200,
                'data' => $config->weixin_pay,
            ];
        } catch (Exception $e) {
            $array = [
                'status' => 404,
                'message' => '获取商户配置失败',
            ];
            return $array;
        }
        return $return;
    }

}