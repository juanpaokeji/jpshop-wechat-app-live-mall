<?php

namespace app\controllers\merchant\forum;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\forum\UserModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class UserController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $forumModel = new UserModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['is_admin'] = 9;
            $array = $forumModel->findAdmin($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UserModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if (isset($params['avatar'])) {
                    if ($params['avatar'] != "") {
                        $str = "./uploads/forum/admin/" . $params['merchant_id'];
                        if (!file_exists($str)) {
                            mkdir($str, 0777);
                        }
                        $base = new Base64Model();
                        $localRes = $base->base64_image_content($params['avatar'], $str);
                        if (!$localRes) {
                            return result(500, "图片格式错误");
                        }
                        $cos = new CosModel();
                        $cosRes = $cos->putObject($localRes);
                        if ($cosRes['status'] == '200') {
                            $url = $cosRes['data'];
                        } else {
                            unlink(Yii::getAlias('@webroot/') . $localRes);
                            return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                        }
                        $params['avatar'] = $url;
                    } else {
                        unset($params['avatar']);
                    }
                }
                $array = $model->updateAdmin($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
