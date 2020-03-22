<?php

namespace app\controllers\admin\system;

use yii;
use yii\web\CommonController;
use app\models\system\DecorationModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

class DecorationController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new DecorationModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            //  $params['status'] = 1;
            $array = $model->do_select($params);
            if ($array['status'] === 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['info'] = json_decode($array['data'][$i]['info']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new DecorationModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            if ($array['status'] === 200) {
                $array['data']['info'] = json_decode($array['data']['info']);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new DecorationModel();
            $must = ['name', 'pic_url', 'appid', 'info', 'is_edit'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            if ($params['pic_url'] != "") {
                $base = new Base64Model();
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/decoration");
                $cos = new CosModel();
                $cosRes = $cos->putObject($params['pic_url']);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
                $params['pic_url'] = $url;
            }
            if ($params['info']) {
                $params['info'] = json_encode($params['info']);
            }
            $array = $model->do_add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new DecorationModel();
            $where['id'] = $id;
            if (isset($params['pic_url'])) {
                if ($params['pic_url'] != "") {
                    $base = new Base64Model();
                    $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/vip");
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($params['pic_url']);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    $params['pic_url'] = $url;
                } else {
                    unset($params['pic_url']);
                }
            }
            if ($params['info']) {
                $params['info'] = json_encode($params['info']);
            }
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new DecorationModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
