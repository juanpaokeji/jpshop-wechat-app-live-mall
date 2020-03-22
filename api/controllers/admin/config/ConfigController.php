<?php

namespace app\controllers\admin\config;

use yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\admin\config\ConfigModel;

/**
 * 抵用卷类型表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class ConfigController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

//    public function actionList() {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
//            $config = new ConfigModel();
//            $array = $config->findall($params); 
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionSingle($id) {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
//            $config = new ConfigModel();
//            $params['id'] = $id;
//            $array = $config->find($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionAdd() {
//        if (yii::$app->request->isPost) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $config = new ConfigModel();
//            $must = ['category_id'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return $rs;
//            }
//            if ($params['type'] == 3) {
//                $params['value'] = json_encode($params['value']);
//            }
//            if ($params['status'] == 1) {
//                setConfig($params['key'], $params['value']);
//            }
//            $array = $config->add($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionUpdate($id) {
//        if (yii::$app->request->isPut) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $params['id'] = $id;
//            $config = new ConfigModel();
//            if (!isset($params['id'])) {
//                return result(400, "缺少参数 id");
//            } else {
//                if (isset($params['type'])) {
//                    if ($params['type'] == 3) {
//                        $params['value'] = json_encode($params['value']);
//                    }
//                }
//                $array = $config->update($params);
//            }
//            if (isset($params['key'])) {
//                if ($params['key']) {
//                    $redis = getConfig($params['key']);
//                    if ($params['status'] == 0) {
//                        Yii::$app->redis->del($params['key']);
//                    } else {
//                        if (!$redis) {
//                            Yii::$app->redis->del($params['key']);
//                            setRedis($params['key'], $params['value']);
//                        }
//                    }
//                }
//            }
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionDelete($id) {
//        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $config = new ConfigModel();
//            $params['id'] = $id;
//            if (!isset($params['id'])) {
//                return result(400, "缺少参数 id");
//            } else {
//                //$rs = $config->find($params);
//                $array = $config->delete($params);
//            }
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

}
