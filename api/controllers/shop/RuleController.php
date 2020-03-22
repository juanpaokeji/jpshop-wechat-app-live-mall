<?php

namespace app\controllers\shop;

use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\AttributeModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class RuleController extends ShopController {

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
//            $model = new PostModel();
//            $params['`key`'] = yii::$app->session['key'];
//            $params['merchant_id'] = yii::$app->session['merchant_id'];
//            $params['user_id'] = yii::$app->session['user_id'];
//            $array = $model->findall($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new AttributeModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new AttributeModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->find($params);
            if ($array['status'] == 200) {
                $stauts = json_decode($array['data']['config'], true);
                return result(200, "请求成功", $stauts);
            } else {
                return result(204, "查询失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionConfig() {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $model = new AttributeModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->find($params);
            if ($array['status'] == 200) {
                $stauts = json_decode($array['data']['config'], true);
                $params['must_keyword'] = isset($params['must_keyword']) ? $params['config']['must_keyword'] : $stauts['must_keyword'];
                $params['must_examine'] = isset($params['must_examine']) ? $params['config']['must_examine'] : $stauts['must_examine'];
                $params['allow_post_time'] = isset($params['allow_post_time']) ? $params['config']['allow_post_time'] : $stauts['allow_post_time'];
                $params['allow_comment_level'] = isset($params['allow_comment_level']) ? $params['config']['allow_comment_level'] : $stauts['allow_comment_level'];
                $params['illegally'] = isset($params['illegally']) ? $params['config']['illegally'] : $stauts['illegally'];
                $params['score'] = isset($params['score']) ? $params['config']['score'] : $stauts['score'];
//                if (isset($params['must_keyword'])) {
//                    $params['config']['must_keyword'] = $params['must_keyword'];
//                } else {
//                    $params['config']['must_keyword'] = $stauts['must_keyword'];
//                }
//                if (isset($params['must_examine'])) {
//                    $params['config']['must_examine'] = $params['must_examine'];
//                } else {
//                    $params['config']['must_examine'] = $stauts['must_examine'];
//                }
//                if (isset($params['allow_post_time'])) {
//                    $params['config']['allow_post_time'] = $params['allow_post_time'];
//                } else {
//                    $params['config']['allow_post_time'] = $stauts['allow_post_time'];
//                }
//                if (isset($params['allow_comment_level'])) {
//                    $params['config']['allow_comment_level'] = $params['allow_comment_level'];
//                } else {
//                    $params['config']['allow_comment_level'] = $stauts['allow_comment_level'];
//                }
//                if (isset($params['illegally'])) {
//                    $params['config']['illegally'] = $params['illegally'];
//                } else {
//                    $params['config']['illegally'] = $stauts['illegally'];
//                }
//               
//                if (isset($params['score'])) {
//                    $params['config']['score'] = $params['score'];
//                } else {
//                    $params['config']['score'] = $stauts['score'];
//                }

                $params['config'] = json_encode($params['config'], JSON_UNESCAPED_UNICODE);
                unset($params['must_keyword']);
                unset($params['must_examine']);
                unset($params['allow_comment_level']);
                unset($params['allow_post_time']);
                unset($params['illegally']);
                unset($params['score']);
                $forum = $model->update($params);
                return $forum;
            } else {
                return result(204, "查询失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new AttributeModel();

            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new AttributeModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);
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
            $model = new AttributeModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
