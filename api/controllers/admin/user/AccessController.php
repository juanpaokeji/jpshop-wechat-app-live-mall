<?php

namespace app\controllers\admin\user;

use Yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\core\TableModel;

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');

class AccessController extends CommonController {

    public $table = "admin_auth_group";
    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 查询列表接口
     * 地址:/admin/access/list
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList() {
        if (yii::$app->request->getIsGet()) {
            $table = new TableModel();
            $app = $table->tableList('admin_auth_group_access', ['delete_time is null' => null]);
            foreach ($app as $a) {
                $a['create_time'] = date('Y-m-d H:i:s', $a['create_time']);
            }
            if (gettype($app) != 'array') {
                return [
                    'status' => '500',
                    'message' => '1004 查询失败',
                ];
            } else {
                return [
                    'status' => '200',
                    'message' => '请求成功',
                    'data' => $app,
                ];
            }
        } else {
            return [
                'status' => '500',
                'message' => '请求方式错误',
            ];
        }
    }

    /**
     * 查询单条接口
     * 地址:/admin/access/single
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionSingle() {
        if (yii::$app->request->getIsGet()) {
            $table = new TableModel();
            $params = yii::$app->request->post(); //获取所有 POST 过来的参数
            $app = $table->tableSingle('admin_auth_group_access', ['id' => $params['id'], 'delete_time is null' => null]);
            $app['create_time'] = date('Y-m-d H:i:s', $app['create_time']);
            if (gettype($app) != 'array') {
                return [
                    'status' => '500',
                    'message' => '1004 查询失败',
                ];
            } else {
                return [
                    'status' => '200',
                    'message' => '请求成功',
                    'data' => $app,
                ];
            }
        } else {
            return [
                'status' => '500',
                'message' => '请求方式错误',
            ];
        }
    }

    /**
     * 新增接口
     * 地址:/admin/access/add
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionAdd() {
        if (yii::$app->request->getIsPost()) {
            $params = yii::$app->request->post(); //获取所有 POST 过来的参数
            foreach ($params as $key => $value) {
                if ($value == "" || !$value) {
                    return [
                        'status' => '500',
                        'message' => '参数不全',
                    ];
                }
            }
            $data = [
                'group_id' => $params['group_id'],
                'status' => 1,
                'rules' => $params['rules'],
                'create_time' => time()
            ];
            $table = new TableModel();
            $res = $table->tableAdd('admin_auth_group_access', $data);

            if (!$res) {
                return [
                    'status' => '500',
                    'message' => '1001 添加失败',
                ];
            } else {
                return [
                    'status' => '200',
                    'message' => '请求成功',
                    'data' => $res
                ];
            }
        } else {
            return [
                'status' => '500',
                'message' => '请求方式错误',
            ];
        }
    }

    /**
     * 删除接口
     * 地址:/admin/access/delete
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionDelete() {
        if (yii::$app->request->getIsDelete()) {
            $params = yii::$app->request->post(); //获取所有 POST 过来的参数
            $where = ['id' => $params['id']];
            unset($params['id']);
            $params['delete_time'] = time();
            $table = new TableModel();
            $res = $table->tableUpdate('admin_auth_group_access', $params, $where);
            if (!$res) {
                return [
                    'status' => '500',
                    'message' => '1002 删除失败',
                ];
            } else {
                return [
                    'status' => '200',
                    'message' => '请求成功',
                    'data' => $res
                ];
            }
        } else {
            return [
                'status' => '500',
                'message' => '请求方式错误',
            ];
        }
    }

    /**
     * 更新接口
     * 地址:/admin/access/update
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionUpdate() {
        if (yii::$app->request->getIsPut()) {
            $params = yii::$app->request->post(); //获取所有 POST 过来的参数
            $where = ['id' => $params['id']];
            unset($params['id']);
            $params['update_time'] = time();
            $table = new TableModel();
            $res = $table->tableUpdate('admin_auth_group_access', $params, $where);
            if (!$res) {
                return [
                    'status' => '500',
                    'message' => '1003 更新失败',
                ];
            } else {
                return [
                    'status' => '200',
                    'message' => '请求成功',
                    'data' => $res
                ];
            }
        } else {
            return [
                'status' => '500',
                'message' => '请求方式错误',
            ];
        }
    }

}
