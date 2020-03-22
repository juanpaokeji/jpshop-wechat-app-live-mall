<?php

namespace app\controllers\merchant\shop;

use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\BannerModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class BannerController extends MerchantController {

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
            $model = new BannerModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
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
            $category = new BannerModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            $array = $category->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new BannerModel();
            $base = new Base64Model();

            //设置类目 参数
            $must = ['name', 'pic_url', 'jump_url', 'type', 'key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['`key`'] = $params['key'];
            unset($params['key']);

            if (isset($params['pic_url'])) {
                $str = creat_mulu("./uploads/shop/banner");
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], $str);
                //将图片上传到cos
                $cos = new CosModel();
                $cosRes = $cos->putObject($params['pic_url']);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                } else {
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
                $params['pic_url'] = $url;
            }
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
            $model = new BannerModel();
            $base = new Base64Model();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);

            if (isset($params['pic_url'])) {
                if ($params['pic_url'] == "") {
                    unset($params['pic_url']);
                } else {
                    $str = creat_mulu("./uploads/shop/banner");
                    $params['pic_url'] = $base->base64_image_content($params['pic_url'], $str);
                    //将图片上传到cos
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($params['pic_url']);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    $params['pic_url'] = $url;
                }
            }

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
            $model = new BannerModel();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
               return $rs;
            }
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
