<?php

namespace app\controllers\merchant\forum;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\forum\KeyWordsModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class KeywordsController extends MerchantController {

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
            $model = new KeyWordsModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['orderby'] = " sort asc";
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new KeyWordsModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new KeyWordsModel();

            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['pic_url'])) {
                if ($params['pic_url'] != "") {
                    $base64 = new Base64Model();
                    $path = creat_mulu('./uploads/forum/keywords');
                    $localRes = $base64->base64_image_content($params['pic_url'], $path);
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
            $model = new KeyWordsModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if (isset($params['pic_url'])) {
                    if ($params['pic_url'] != "") {
                        $base64 = new Base64Model();
                        $path = creat_mulu('./uploads/forum/keywords');
                        $localRes = $base64->base64_image_content($params['pic_url'], $path);
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
            $model = new KeyWordsModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
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
