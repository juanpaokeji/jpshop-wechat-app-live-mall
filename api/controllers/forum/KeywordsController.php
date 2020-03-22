<?php

namespace app\controllers\forum;

use yii;
use yii\web\ForumController;
use yii\db\Exception;
use app\models\forum\KeyWordsModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class KeywordsController extends ForumController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $comment = new KeyWordsModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['status'] = 1;
            $params['is_admin'] = 0;
            $params['orderby'] = " sort asc";

            $array = $comment->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $comment = new KeyWordsModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $array = $comment->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $comment = new KeyWordsModel();
            //设置类目 参数
            $must = ['post_id'];
            $rs = $this->checkInput($must, $params);
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['pic_url'])) {
                if ($params['pic_url'] != "") {
                    $model = new Base64Model();
                    $path = creat_mulu();
                    $localRes = $model->base64_image_content($params['pic_url'], $path);
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($localRes);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                        unlink(Yii::getAlias('@webroot/') . $localRes);
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $localRes);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    $params['pic_url'] = $url;
                } else {
                    unset($params['pic_url']);
                }
            }
            $array = $comment->add($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $comment = new KeyWordsModel();

            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            if (!isset($params['id'])) {
                result(400, "缺少参数 id");
            } else {
                if (isset($params['pic_url'])) {
                    if ($params['pic_url'] != "") {
                        $model = new Base64Model();
                        $path = creat_mulu();
                        $localRes = $model->base64_image_content($params['pic_url'], $path);
                        $cos = new CosModel();
                        $cosRes = $cos->putObject($localRes);
                        if ($cosRes['status'] == '200') {
                            $url = $cosRes['data'];
                            unlink(Yii::getAlias('@webroot/') . $localRes);
                        } else {
                            unlink(Yii::getAlias('@webroot/') . $localRes);
                            return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                        }
                        $params['pic_url'] = $url;
                    } else {
                        unset($params['pic_url']);
                    }
                }
                $array = $comment->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
