<?php

namespace app\controllers\merchant\forum;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\forum\PostModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\merchant\user\UserModel;
use app\models\merchant\forum\CommentModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class PostController extends MerchantController {

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
            $model = new PostModel();
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
            $model = new PostModel();
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
            $model = new PostModel();
            //设置类目 参数
            $must = ['title', 'content'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (isset($params['pic_urls'])) {
                $str = creat_mulu("./uploads/forum");
                $base = new Base64Model();
                $localRes = $base->base64_image_content($params['pic_urls'], $str);
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
                $params['pic_urls'] = $url;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $userModel = new UserModel();
            $dataWhere['is_admin'] = 9;
            $dataWhere['merchant_id'] = yii::$app->session['uid'];
            $dataWhere['`key`'] = $params['`key`'];
            $user = $userModel->find($dataWhere);
            if ($user['status'] == 200) {
                $params['user_id'] = $user['data']['id'];
                $params['comment_time'] = time();
                $array = $model->add($params);
                return $array;
            } else {
                return result(500, "未绑定用户");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PostModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
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

    public function actionUpdatemore() {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PostModel();
            $val = "";
            foreach ($params['ids'] as $value) {
                $val = $val . $value . ",";
            }
            $val = substr($val, 0, -1);
            unset($params['ids']);
            $params['id'] = $val;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->updateMore($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new PostModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
                if ($array['status'] == 200) {
                    $commentModel = new CommentModel();
                    $data['post_id'] = $id;
                    $data['`key`'] = $params['`key`'];
                    unset($params['key']);
                    $data['merchant_id'] = yii::$app->session['uid'];
                    $commentModel->delete($data);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
