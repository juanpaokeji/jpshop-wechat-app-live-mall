<?php

namespace app\controllers\merchant\design;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\design\OrderModel;
use app\models\core\CosModel;
use app\models\core\Base64Model;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class OrderController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['add', 'update'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $orderModel = new OrderModel();
            $array = $orderModel->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $orderModel = new OrderModel();
            $params['id'] = $id;
            $array = $orderModel->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $orderModel = new OrderModel();
            //设置类目 参数
            $must = ['design_str'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            if ($params['design_str']) {
                $params['design_str'] = htmlentities($params['design_str']);
                $base = new Base64Model();
                $str = creat_mulu("./uploads/material");

                $localRes = $base->base64_image_content($params['design_img'], $str);
                if (!$localRes) {
                    return result(500, "图片格式错误");
                }
                //将图片上传到cos
                $cos = new CosModel();
                $cosRes = $cos->putObject($localRes);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                } else {
                    unlink(Yii::getAlias('@webroot/') . $localRes);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
                $params['design_img'] = $url;
                $array = $orderModel->add($params);
            } else {
                return result(400, "缺少参数 DIV");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $orderModel = new OrderModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $orderModel->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUp($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $orderModel = new OrderModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $orderModel->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $orderModel = new OrderModel();
            $params['id'] = $id;
            $array = $orderModel->delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
