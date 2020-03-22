<?php

namespace app\controllers\forum;

use yii;
use yii\web\ForumController;
use yii\db\Exception;
use app\models\forum\UserLikeModel;
use app\models\forum\PostModel;
use app\models\forum\CommentModel;
use app\models\forum\UserModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class LikeController extends ForumController {

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
            $model = new UserLikeModel();
            $must = ['page'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new UserLikeModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
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
            $model = new UserLikeModel();

            //设置类目 参数
            $must = ['status', 'source_id', 'type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }


            $userModel = new UserModel();
            $user = $userModel->find(['id' => yii::$app->session['user_id']]);
            if ($user['status'] != 200) {
                return result(500, "用户信息查询不到！");
            }
            if ($user['data']['status'] == 0) {
                return result(500, "您已被拉入黑名单");
            }

            $data['source_id'] = $params['source_id'];
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];

            $single = $model->find($data);

            if ($single['status'] == 200) {
                $params['`key`'] = yii::$app->session['key'];
                $params['merchant_id'] = yii::$app->session['merchant_id'];
                $params['user_id'] = yii::$app->session['user_id'];
                $array = $model->update($params);
            } else {
                $params['`key`'] = yii::$app->session['key'];
                $params['merchant_id'] = yii::$app->session['merchant_id'];
                $params['user_id'] = yii::$app->session['user_id'];
                $params['status'] = 1;
                $array = $model->add($params);
            }
            if ($params['type'] == 1) {
                $data['status'] = 1;
                unset($data['user_id']);
                $likes = $model->likeCount($data);
                $post = new PostModel;
                $postData['like_count'] = $likes;
                $postData['id'] = $params['source_id'];
                $rs = $post->updatePost($postData);
            }
            if ($params['type'] == 2) {
                $data['type'] = 2;
                $data['status'] = 1;
                unset($data['user_id']);
                $likes = $model->likeCount($data);
                $comment = new CommentModel;
                $postData['like_count'] = $likes;
                $postData['id'] = $params['source_id'];
                $rs = $comment->updateComment($postData);
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
            $model = new UserLikeModel();
            $params['source_id'] = $id;
            if (!isset($params['source_id'])) {
                return result(400, "缺少参数 source_id");
            } else {
                $params['`key`'] = yii::$app->session['key'];
                $params['merchant_id'] = yii::$app->session['merchant_id'];
                $params['user_id'] = yii::$app->session['user_id'];
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
            $model = new UserLikeModel();
            $params['id'] = $id;
            $params['user_id'] = yii::$app->session['user_id'];
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
